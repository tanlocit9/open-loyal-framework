<?php

namespace OpenLoyalty\Component\Account\Domain\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class PointsTransferCannotBeExpiredException.
 */
class PointsTransferCannotBeExpiredException extends \InvalidArgumentException implements Translatable
{
    /**
     * @var string
     */
    protected $id;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->message = sprintf('Points transfer #%s cannot be expired', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'account.points_transfer.cannot_be_expired';
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
