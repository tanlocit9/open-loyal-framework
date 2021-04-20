<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class LoyaltyCardNumberAlreadyExistsException.
 */
class LoyaltyCardNumberAlreadyExistsException extends CustomerValidationException implements Translatable
{
    protected $message = 'Customer with such loyalty card number already exists';

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'customer.registration.loyalty_card_number_exists';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams(): array
    {
        return [];
    }
}
