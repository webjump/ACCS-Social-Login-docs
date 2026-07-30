<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Webjump\SocialLogin\Api\JwtAuthenticationInterface;
use Webjump\SocialLogin\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class JwtAuthentication
 * @package Webjump\SocialLogin\Model
 */
class JwtAuthentication implements JwtAuthenticationInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function authenticateByJwt(string $token): CustomerInterface
    {
        // Validate JWT token
        $payload = $this->validateJwtToken($token);
        if (!$payload) {
            throw new LocalizedException(__('Invalid JWT token'));
        }

        // Extract customer data from JWT
        $customerData = $this->extractCustomerDataFromJwt($token);

        // Find or create customer
        $customer = $this->findOrCreateCustomer($customerData);

        // Create customer session with fallback retry mechanism
        $this->createCustomerSessionWithRetry($customer);

        return $customer;
    }

    /**
     * @inheritDoc
     */
    public function validateJwtToken(string $token)
    {
        try {
            // Split JWT token into parts
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }

            // Decode header and payload
            $header = json_decode(base64_decode($parts[0]), true);
            $payload = json_decode(base64_decode($parts[1]), true);

            if (!$header || !$payload) {
                return false;
            }

            // Check token expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                $this->logger->warning('JWT token expired');
                return false;
            }

            // Here you should add your JWT signature verification logic
            // For now, we'll validate basic structure and expiration

            return $payload;

        } catch (\Exception $e) {
            $this->logger->error('JWT validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function extractCustomerDataFromJwt(string $token): array
    {
        $payload = $this->validateJwtToken($token);
        if (!$payload) {
            throw new LocalizedException(__('Cannot extract data from invalid JWT token'));
        }

        // Extract customer information from JWT payload
        $customerData = [
            'email' => $payload['email'] ?? null,
            'firstname' => $payload['given_name'] ?? $payload['first_name'] ?? $payload['name'] ?? 'Social',
            'lastname' => $payload['family_name'] ?? $payload['last_name'] ?? 'User',
            'external_id' => $payload['sub'] ?? $payload['id'] ?? null,
            'provider' => $payload['provider'] ?? 'social',
            'picture' => $payload['picture'] ?? null,
        ];

        if (empty($customerData['email'])) {
            throw new LocalizedException(__('Email is required in JWT token'));
        }

        return $customerData;
    }

    /**
     * Find existing customer or create new one
     *
     * @param array $customerData
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function findOrCreateCustomer(array $customerData): CustomerInterface
    {
        $websiteId = $this->storeManager->getWebsite()->getId();

        try {
            // Try to find existing customer by email
            $customer = $this->customerRepository->get($customerData['email'], $websiteId);

            // Update customer data if needed
            if ($customer->getFirstname() !== $customerData['firstname'] ||
                $customer->getLastname() !== $customerData['lastname']) {
                $customer->setFirstname($customerData['firstname']);
                $customer->setLastname($customerData['lastname']);
                $customer = $this->customerRepository->save($customer);
            }

            return $customer;

        } catch (NoSuchEntityException $e) {
            // Customer doesn't exist, create new one if auto-create is enabled
            if (!$this->helper->getAutoCreateCustomer()) {
                throw new LocalizedException(__('Customer account not found. Please register first.'));
            }

            return $this->createNewCustomer($customerData, $websiteId);
        }
    }

    /**
     * Create new customer account
     *
     * @param array $customerData
     * @param int $websiteId
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function createNewCustomer(array $customerData, int $websiteId): CustomerInterface
    {
        try {
            $customer = $this->customerFactory->create();
            $customer->setEmail($customerData['email']);
            $customer->setFirstname($customerData['firstname']);
            $customer->setLastname($customerData['lastname']);
            $customer->setWebsiteId($websiteId);
            $customer->setStoreId($this->storeManager->getStore()->getId());

            // Set custom attributes if needed
            if (!empty($customerData['external_id'])) {
                $customer->setCustomAttribute('social_login_id', $customerData['external_id']);
            }
            if (!empty($customerData['provider'])) {
                $customer->setCustomAttribute('social_login_provider', $customerData['provider']);
            }

            return $this->customerRepository->save($customer);

        } catch (\Exception $e) {
            $this->logger->error('Error creating customer: ' . $e->getMessage());
            throw new LocalizedException(__('Unable to create customer account'));
        }
    }

    /**
     * Create customer session in Magento
     *
     * @param CustomerInterface $customer
     * @throws LocalizedException
     */
    private function createCustomerSession(CustomerInterface $customer): void
    {
        try {
            // Start PHP session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                $this->logger->info('PHP session started for customer: ' . $customer->getEmail());
            }

            // Get current session ID before login
            $oldSessionId = session_id();
            $this->logger->info('Old session ID: ' . $oldSessionId);

            // Log in the customer using Magento's session system
            $this->customerSession->setCustomerDataAsLoggedIn($customer);

            // Force session regeneration and ensure it's saved
            $this->customerSession->regenerateId();

            // Get new session ID
            $newSessionId = session_id();
            $this->logger->info('New session ID: ' . $newSessionId);

            // Verify session was created successfully
            if (!$this->customerSession->isLoggedIn()) {
                throw new LocalizedException(__('Failed to create customer session'));
            }

            // Force session write and close to ensure persistence
            session_write_close();

            // Restart session to verify persistence
            session_start();

            // Double check customer is still logged in
            if (!$this->customerSession->isLoggedIn()) {
                $this->logger->error('Session lost after restart for customer: ' . $customer->getEmail());
                throw new LocalizedException(__('Session persistence failed'));
            }

            $this->logger->info('Customer session successfully created and verified for: ' . $customer->getEmail());

        } catch (\Exception $e) {
            $this->logger->error('Error creating customer session: ' . $e->getMessage());
            $this->logger->error('Stack trace: ' . $e->getTraceAsString());
            throw new LocalizedException(__('Unable to create customer session: ' . $e->getMessage()));
        }
    }

    /**
     * Create customer session with retry mechanism
     *
     * @param CustomerInterface $customer
     * @throws LocalizedException
     */
    private function createCustomerSessionWithRetry(CustomerInterface $customer): void
    {
        $maxRetries = 3;
        $retryCount = 0;
        $lastException = null;

        while ($retryCount < $maxRetries) {
            try {
                $this->createCustomerSession($customer);

                // If we get here, session was created successfully
                $this->logger->info('Customer session created successfully on attempt: ' . ($retryCount + 1));
                return;

            } catch (\Exception $e) {
                $retryCount++;
                $lastException = $e;
                $this->logger->warning('Session creation attempt ' . $retryCount . ' failed: ' . $e->getMessage());

                if ($retryCount < $maxRetries) {
                    // Wait a short time before retry
                    usleep(100000); // 100ms

                    // Clear any existing session data before retry
                    $this->customerSession->logout();

                    $this->logger->info('Retrying session creation for customer: ' . $customer->getEmail());
                }
            }
        }

        // If we get here, all retries failed
        $this->logger->error('All session creation attempts failed for customer: ' . $customer->getEmail());
        throw new LocalizedException(__('Unable to create customer session after ' . $maxRetries . ' attempts: ' . $lastException->getMessage()));
    }
}