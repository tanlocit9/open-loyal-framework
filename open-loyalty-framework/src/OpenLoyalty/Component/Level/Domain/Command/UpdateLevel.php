<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Domain\Command;

use OpenLoyalty\Component\Level\Domain\LevelId;

/**
 * Class UpdateLevel.
 */
class UpdateLevel extends LevelCommand
{
    /**
     * @var array
     */
    protected $levelData;

    /**
     * UpdateLevel constructor.
     *
     * @param LevelId $levelId
     * @param array   $levelData
     */
    public function __construct(LevelId $levelId, array $levelData)
    {
        parent::__construct($levelId);
        $this->levelData = $levelData;
    }

    /**
     * @return array
     */
    public function getLevelData()
    {
        return $this->levelData;
    }
}
