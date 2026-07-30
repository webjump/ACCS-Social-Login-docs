<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Opens an Adobe Commerce customer session from the customer token issued by the
 * Social Login App Builder application.
 *
 * The token is a genuine Adobe Commerce customer access token - the same kind
 * `POST /V1/integration/customer/token` returns - so it is validated against
 * Commerce's own token storage. No part of the customer's identity is taken from
 * data supplied by the browser.
 */
interface CustomerTokenAuthenticationInterface
{
    /**
     * Validate a Commerce customer token and log the matching customer in.
     *
     * @param string $token Commerce customer access token.
     * @return CustomerInterface
     * @throws LocalizedException If the token is unknown, revoked, expired, or
     *         does not belong to a customer.
     */
    public function authenticateByToken(string $token): CustomerInterface;

    /**
     * Resolve the customer id a Commerce customer token belongs to.
     *
     * @param string $token Commerce customer access token.
     * @return int
     * @throws LocalizedException If the token is not a valid customer token.
     */
    public function resolveCustomerId(string $token): int;
}
