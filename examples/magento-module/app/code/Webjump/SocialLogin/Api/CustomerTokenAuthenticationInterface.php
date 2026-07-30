<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface JwtAuthenticationInterface
 * @package Webjump\SocialLogin\Api
 */
interface JwtAuthenticationInterface
{
    /**
     * Authenticate customer by JWT token and create session
     *
     * @param string $token
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function authenticateByJwt(string $token): CustomerInterface;

    /**
     * Validate JWT token
     *
     * @param string $token
     * @return array|false
     */
    public function validateJwtToken(string $token);

    /**
     * Extract customer data from JWT
     *
     * @param string $token
     * @return array
     * @throws LocalizedException
     */
    public function extractCustomerDataFromJwt(string $token): array;
}