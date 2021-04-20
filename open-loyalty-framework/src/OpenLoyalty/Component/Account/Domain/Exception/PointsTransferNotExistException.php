<?php

namespace OpenLoyalty\Component\Account\Domain\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class PointsTransferNotExistException.
 */
class PointsTransferNotExistException extends \InvalidArgumentException implements Translatable
{
    /**
     * @var string
     */
    protected $id;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->message = sprintf('Points transfer #%s does not exist', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'account.points_transfer.not_exist';
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
