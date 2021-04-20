<?php

namespace OpenLoyalty\Component\Customer\Infrastructure\Exception;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;

/**
 * Class LevelDowngradeModeNotSupportedException.
 */
class LevelDowngradeModeNotSupportedException extends \Exception implements Translatable
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'customer.level.downgrade_mode.not_supported';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams(): array
    {
        return [];
    }
}
