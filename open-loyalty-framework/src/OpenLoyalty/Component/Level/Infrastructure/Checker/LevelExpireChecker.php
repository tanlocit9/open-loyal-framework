<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Level\Infrastructure\Checker;

use OpenLoyalty\Component\Customer\Infrastructure\Exception\LevelDowngradeModeNotSupportedException;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use OpenLoyalty\Component\Customer\Infrastructure\TierAssignTypeProvider;
use OpenLoyalty\Component\Level\Domain\Level;

/**
 * Class LevelExpireChecker.
 */
class LevelExpireChecker implements LevelExpireCheckerInterface
{
    /**
     * @var LevelDowngradeModeProvider
     */
    private $levelDowngradeModeProvider;

    /**
     * @var TierAssignTypeProvider
     */
    private $tierAssignTypeProvider;

    /**
     * LevelExpireChecker constructor.
     *
     * @param LevelDowngradeModeProvider $levelDowngradeModeProvider
     * @param TierAssignTypeProvider     $tierAssignTypeProvider
     */
    public function __construct(
        LevelDowngradeModeProvider $levelDowngradeModeProvider,
        TierAssignTypeProvider $tierAssignTypeProvider
    ) {
        $this->levelDowngradeModeProvider = $levelDowngradeModeProvider;
        $this->tierAssignTypeProvider = $tierAssignTypeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function checkLevelExpiryOnDate(
        Level $level,
        \DateTimeInterface $lastLevelRecalculation,
        \DateTimeInterface $expireDate
    ): bool {
        if (!$this->displayDowngradeModeXDaysStats()) {
            return false;
        }

        $nextDate = (clone $lastLevelRecalculation)->modify(sprintf('+%u days', $this->levelDowngradeModeProvider->getDays()));
        $currentDate = new \DateTime();

        if ($nextDate < $currentDate) {
            $days = 0;
        } else {
            $diff = abs($nextDate->getTimestamp() - $currentDate->getTimestamp());
            $days = ceil($diff / 86400);
        }

        $expireDiff = abs($expireDate->getTimestamp() - $currentDate->getTimestamp());
        $expireDays = ceil($expireDiff / 86400);

        return $expireDays === $days;
    }

    /**
     * @return bool
     */
    private function displayDowngradeModeXDaysStats(): bool
    {
        try {
            return
                $this->tierAssignTypeProvider->getType() === TierAssignTypeProvider::TYPE_POINTS &&
                $this->levelDowngradeModeProvider->getMode() === LevelDowngradeModeProvider::MODE_X_DAYS
                ;
        } catch (LevelDowngradeModeNotSupportedException $e) {
            return false;
        }
    }
}
