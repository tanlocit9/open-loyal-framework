<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Infrastructure;

use OpenLoyalty\Component\Customer\Infrastructure\Exception\LevelDowngradeModeNotSupportedException;

/**
 * Interface LevelDowngradeModeProvider.
 */
interface LevelDowngradeModeProvider
{
    const DEFAULT_DAYS = 365;

    const MODE_NONE = 'none';
    const MODE_AUTO = 'automatic';
    const MODE_X_DAYS = 'after_x_days';

    const BASE_NONE = 'none';
    const BASE_ACTIVE_POINTS = 'active_points';
    const BASE_EARNED_POINTS = 'earned_points';
    const BASE_EARNED_POINTS_SINCE_LAST_LEVEL_CHANGE = 'earned_points_since_last_level_change';

    /**
     * @return string
     *
     * @throws LevelDowngradeModeNotSupportedException
     */
    public function getMode(): string;

    /**
     * @return string
     *
     * @throws LevelDowngradeModeNotSupportedException
     */
    public function getBase(): string;

    /**
     * @return int
     */
    public function getDays(): int;

    /**
     * @return bool
     */
    public function isResettingPointsEnabled(): bool;
}
