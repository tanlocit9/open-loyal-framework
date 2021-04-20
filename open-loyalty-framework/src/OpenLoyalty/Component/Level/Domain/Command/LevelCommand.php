<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Domain\Command;

use OpenLoyalty\Component\Level\Domain\LevelId;

/**
 * Class LevelCommand.
 */
class LevelCommand
{
    /**
     * @var LevelId
     */
    protected $levelId;

    /**
     * LevelCommand constructor.
     *
     * @param LevelId $levelId
     */
    public function __construct(LevelId $levelId)
    {
        $this->levelId = $levelId;
    }

    /**
     * @return LevelId
     */
    public function getLevelId()
    {
        return $this->levelId;
    }
}
