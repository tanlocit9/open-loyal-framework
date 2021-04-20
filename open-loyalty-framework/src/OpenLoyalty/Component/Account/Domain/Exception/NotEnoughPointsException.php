<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class NotEnoughPointsException.
 */
class NotEnoughPointsException extends \InvalidArgumentException implements Translatable
{
    protected $message = 'Not enough points';

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'account.points_transfer.not_enough_points';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams(): array
    {
        return [];
    }
}
