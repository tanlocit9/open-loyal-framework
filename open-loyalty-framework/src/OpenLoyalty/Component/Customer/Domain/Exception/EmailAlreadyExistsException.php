<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class EmailAlreadyExistsException.
 */
class EmailAlreadyExistsException extends CustomerValidationException implements Translatable
{
    protected $message = 'Customer with such email already exists';

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'customer.registration.email_exists';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams(): array
    {
        return [];
    }
}
