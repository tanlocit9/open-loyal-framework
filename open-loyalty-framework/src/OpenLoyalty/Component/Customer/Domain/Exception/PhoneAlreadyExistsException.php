<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class PhoneAlreadyExistsException.
 */
class PhoneAlreadyExistsException extends CustomerValidationException implements Translatable
{
    protected $message = 'Customer with such phone already exists.';

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'customer.registration.phone_number_exists';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams(): array
    {
        return [];
    }
}
