<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Domain\Command;

use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto;

/**
 * Class SetLevelPhoto.
 */
class SetLevelPhoto extends LevelCommand
{
    /**
     * @var LevelPhoto
     */
    protected $levelPhoto;

    /**
     * SetLevelPhoto constructor.
     *
     * @param LevelId         $levelId
     * @param LevelPhoto|null $levelPhoto
     */
    public function __construct(LevelId $levelId, LevelPhoto $levelPhoto = null)
    {
        parent::__construct($levelId);
        $this->levelPhoto = $levelPhoto;
    }

    /**
     * @return LevelPhoto
     */
    public function getLevelPhoto()
    {
        return $this->levelPhoto;
    }
}
