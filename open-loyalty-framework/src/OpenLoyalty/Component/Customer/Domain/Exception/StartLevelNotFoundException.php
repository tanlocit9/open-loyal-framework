<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class StartLevelNotFoundException.
 */
class StartLevelNotFoundException extends CustomerValidationException implements Translatable
{
    protected $message = 'Neither level is not available as start level for this customer.';

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'customer.registration.start_level_not_found';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams(): array
    {
        return [];
    }
}
