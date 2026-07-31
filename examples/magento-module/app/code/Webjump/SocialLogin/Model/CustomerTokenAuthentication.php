<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Psr\Log\LoggerInterface;
use Webjump\SocialLogin\Api\CustomerTokenAuthenticationInterface;

/**
 * Logs a customer in from the Adobe Commerce customer token issued by the Social
 * Login App Builder application.
 *
 * Security model: the token is a real Commerce customer access token, so it is
 * looked up in Commerce's own token storage (the same source
 * `Magento\Webapi\Model\Authorization\TokenUserContext` uses for REST requests)
 * and the customer id comes from that record. A forged or guessed token has no
 * matching row and is refused - the browser never gets to state who it is.
 *
 * This is why the module must NOT accept an identity payload (an email, a signed
 * blob, a JWT) from the client: doing so would move the trust decision to
 * whoever is calling the endpoint.
 */
class CustomerTokenAuthentication implements CustomerTokenAuthenticationInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $customerSession
     * @param TokenFactory $tokenFactory
     * @param OauthHelper $oauthHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        TokenFactory $tokenFactory,
        OauthHelper $oauthHelper,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->tokenFactory = $tokenFactory;
        $this->oauthHelper = $oauthHelper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function authenticateByToken(string $token): CustomerInterface
    {
        $customerId = $this->resolveCustomerId($token);

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            // The token references a customer that no longer exists.
            throw new LocalizedException(__('Invalid authentication token.'));
        }

        $this->createCustomerSession($customer);

        return $customer;
    }

    /**
     * @inheritDoc
     */
    public function resolveCustomerId(string $token): int
    {
        $token = trim($token);
        if ($token === '') {
            throw new LocalizedException(__('Invalid authentication token.'));
        }

        /** @var Token $tokenModel */
        $tokenModel = $this->tokenFactory->create();
        $tokenModel->loadByToken($token);

        // Every failure below is reported with the same generic message on
        // purpose - telling a caller *why* a token was refused helps them probe.
        if (!$tokenModel->getId() || $tokenModel->getRevoked()) {
            $this->logger->warning('Social Login: unknown or revoked customer token presented');
            throw new LocalizedException(__('Invalid authentication token.'));
        }

        if ((int)$tokenModel->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            // An admin or integration token must never open a customer session.
            $this->logger->warning('Social Login: token presented is not a customer token');
            throw new LocalizedException(__('Invalid authentication token.'));
        }

        $customerId = (int)$tokenModel->getCustomerId();
        if ($customerId <= 0) {
            $this->logger->warning('Social Login: customer token carries no customer id');
            throw new LocalizedException(__('Invalid authentication token.'));
        }

        if ($this->isExpired($tokenModel)) {
            $this->logger->warning('Social Login: expired customer token presented');
            throw new LocalizedException(__('Invalid authentication token.'));
        }

        return $customerId;
    }

    /**
     * Whether the token is past the customer token lifetime configured in
     * Stores > Configuration > Services > OAuth > Access Token Expiration.
     *
     * @param Token $tokenModel
     * @return bool
     */
    private function isExpired(Token $tokenModel): bool
    {
        $lifetimeHours = (int)$this->oauthHelper->getCustomerTokenLifetime();
        if ($lifetimeHours <= 0) {
            // 0 means "never expires" in Commerce's configuration.
            return false;
        }

        $createdAt = strtotime((string)$tokenModel->getCreatedAt());
        if (!$createdAt) {
            // No usable creation date - treat as expired rather than trusting it.
            return true;
        }

        return ($createdAt + ($lifetimeHours * 3600)) < time();
    }

    /**
     * Open the Magento customer session.
     *
     * @param CustomerInterface $customer
     * @throws LocalizedException
     */
    private function createCustomerSession(CustomerInterface $customer): void
    {
        // regenerateId() before logging in prevents session fixation: a session
        // id an attacker planted in the browser cannot survive into the
        // authenticated session.
        $this->customerSession->regenerateId();
        $this->customerSession->setCustomerDataAsLoggedIn($customer);

        if (!$this->customerSession->isLoggedIn()) {
            $this->logger->error('Social Login: failed to open a session for customer ' . $customer->getId());
            throw new LocalizedException(__('Unable to create customer session.'));
        }
    }
}
