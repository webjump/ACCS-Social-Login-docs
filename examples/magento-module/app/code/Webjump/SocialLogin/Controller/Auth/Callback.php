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
use Webjump\SocialLogin\Api\JwtAuthenticationInterface;
use Webjump\SocialLogin\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Callback
 * @package Webjump\SocialLogin\Controller\Auth
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
     * @var JwtAuthenticationInterface
     */
    private $jwtAuthentication;

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
     * @param JwtAuthenticationInterface $jwtAuthentication
     * @param Data $helper
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        JwtAuthenticationInterface $jwtAuthentication,
        Data $helper,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->jwtAuthentication = $jwtAuthentication;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Execute JWT authentication and create Magento session
     *
     * @return Json|Redirect
     */
    public function execute()
    {
        try {
            // Log the request method and content type
            $this->logger->info('Callback request method: ' . $this->request->getMethod());
            $this->logger->info('Callback content type: ' . $this->request->getHeader('Content-Type'));

            // Try to get token from different sources
            $token = $this->request->getParam('token');

            // If not found in params, try to get from POST body (JSON)
            if (!$token && $this->request->getMethod() === 'POST') {
                $postData = $this->request->getContent();
                $this->logger->info('Raw POST data: ' . $postData);

                if ($postData) {
                    $jsonData = json_decode($postData, true);
                    if ($jsonData && isset($jsonData['token'])) {
                        $token = $jsonData['token'];
                        $this->logger->info('Token extracted from JSON body');
                    }
                }
            }

            $returnUrl = $this->request->getParam('return_url');

            // Also try return_url from JSON if not found in params
            if (!$returnUrl && isset($jsonData['return_url'])) {
                $returnUrl = $jsonData['return_url'];
            }

            $this->logger->info('Extracted token: ' . ($token ? 'YES' : 'NO'));
            $this->logger->info('Extracted return_url: ' . ($returnUrl ?: 'NONE'));

            if (!$token) {
                throw new LocalizedException(__('JWT token is required'));
            }

            // Authenticate using JWT and create customer session
            $customer = $this->jwtAuthentication->authenticateByJwt($token);

            // Log successful authentication
            $this->logger->info('JWT authentication successful for customer: ' . $customer->getEmail());

            if ($this->request->isAjax()) {
                /** @var Json $result */
                $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                return $result->setData([
                    'success' => true,
                    'customer_id' => $customer->getId(),
                    'session_id' => session_id(),
                    'redirect_url' => $returnUrl ?: $this->helper->getRedirectUrl()
                ]);
            }

            $this->messageManager->addSuccessMessage(__('You have been successfully logged in.'));

            /** @var Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirectUrl = $returnUrl ?: $this->helper->getRedirectUrl() ?: '/customer/account';

            return $result->setUrl($redirectUrl);

        } catch (LocalizedException $e) {
            $this->logger->error('Social Login JWT Authentication Error: ' . $e->getMessage());

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