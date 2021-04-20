<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Level\Infrastructure\Checker;

use OpenLoyalty\Component\Level\Domain\Level;

/**
 * Interface LevelExpireCheckerInterface.
 */
interface LevelExpireCheckerInterface
{
    /**
     * @param Level              $level
     * @param \DateTimeInterface $lastLevelRecalculation
     * @param \DateTimeInterface $expireDate
     *
     * @return bool
     */
    public function checkLevelExpiryOnDate(
        Level $level,
        \DateTimeInterface $lastLevelRecalculation,
        \DateTimeInterface $expireDate
    ): bool;
}
