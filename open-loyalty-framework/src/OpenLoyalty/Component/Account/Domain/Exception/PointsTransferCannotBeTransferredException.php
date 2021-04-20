<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class PointsTransferCannotBeTransferredException.
 */
class PointsTransferCannotBeTransferredException extends \Exception implements Translatable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * PointsTransferCannotBeTransferredException constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->message = sprintf('Points transfer #%s cannot be transferred', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'account.points_transfer.cannot_be_transferred';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams(): array
    {
        return [
            '%id%' => $this->id,
        ];
    }
}
