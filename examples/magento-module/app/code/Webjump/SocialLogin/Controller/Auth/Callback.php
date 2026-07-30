<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Controller\Auth;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Webjump\SocialLogin\Api\CustomerTokenAuthenticationInterface;
use Webjump\SocialLogin\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Receives the Adobe Commerce customer token produced by the Social Login App
 * Builder application and opens the matching customer session.
 *
 * The token is validated against Commerce's own token storage, so this endpoint
 * cannot be used to log in as an arbitrary customer. It deliberately accepts
 * nothing but the token - no email, no profile data.
 */
class Callback implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var CustomerTokenAuthenticationInterface
     */
    private $tokenAuthentication;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param CustomerTokenAuthenticationInterface $tokenAuthentication
     * @param Data $helper
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        CustomerTokenAuthenticationInterface $tokenAuthentication,
        Data $helper,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->tokenAuthentication = $tokenAuthentication;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Validate the Commerce customer token and open the customer session.
     *
     * @return Json|Redirect
     */
    public function execute()
    {
        try {
            $jsonData = [];

            // Accept the token from a query/form parameter or from a JSON body,
            // so the endpoint works for both the AJAX and redirect integrations.
            $token = $this->request->getParam('token');
            $returnUrl = $this->request->getParam('return_url');

            if ((!$token || !$returnUrl) && $this->request->getMethod() === 'POST') {
                // Never log the raw body: it carries the customer token.
                $postData = $this->request->getContent();
                if ($postData) {
                    $decoded = json_decode($postData, true);
                    if (is_array($decoded)) {
                        $jsonData = $decoded;
                    }
                }

                $token = $token ?: ($jsonData['token'] ?? null);
                $returnUrl = $returnUrl ?: ($jsonData['return_url'] ?? null);
            }

            if (!$token) {
                throw new LocalizedException(__('An authentication token is required.'));
            }

            // Validated against Commerce's token storage - see
            // CustomerTokenAuthentication for why nothing else is trusted here.
            $customer = $this->tokenAuthentication->authenticateByToken((string)$token);

            $this->logger->info('Social Login: session opened for customer ' . $customer->getId());

            if ($this->request->isAjax()) {
                /** @var Json $result */
                $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                return $result->setData([
                    'success' => true,
                    'customer_id' => $customer->getId(),
                    'redirect_url' => $returnUrl ?: $this->helper->getRedirectUrl()
                ]);
            }

            $this->messageManager->addSuccessMessage(__('You have been successfully logged in.'));

            /** @var Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirectUrl = $returnUrl ?: $this->helper->getRedirectUrl() ?: '/customer/account';

            return $result->setUrl($redirectUrl);

        } catch (LocalizedException $e) {
            $this->logger->error('Social Login authentication error: ' . $e->getMessage());

            if ($this->request->isAjax()) {
                /** @var Json $result */
                $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                return $result->setData([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            $this->messageManager->addErrorMessage($e->getMessage());

            /** @var Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $result->setUrl('/customer/account/login');

        } catch (\Exception $e) {
            $this->logger->error('Social Login Unexpected Error: ' . $e->getMessage());

            if ($this->request->isAjax()) {
                /** @var Json $result */
                $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                return $result->setData([
                    'success' => false,
                    'message' => __('An error occurred during authentication.')
                ]);
            }

            $this->messageManager->addErrorMessage(__('An error occurred during authentication.'));

            /** @var Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $result->setUrl('/customer/account/login');
        }
    }
}