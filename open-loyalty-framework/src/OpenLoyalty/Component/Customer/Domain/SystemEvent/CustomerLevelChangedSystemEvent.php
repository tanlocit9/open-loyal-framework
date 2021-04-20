<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\SystemEvent;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\LevelId;

/**
 * Class CustomerLevelChangedSystemEvent.
 */
class CustomerLevelChangedSystemEvent extends CustomerSystemEvent
{
    private const LEVEL_GOES_UP = 'up';
    private const LEVEL_GOES_DOWN = 'down';

    /**
     * @var LevelId
     */
    private $levelId;

    /**
     * @var null|string
     */
    private $levelName;

    /**
     * @var null|bool
     */
    private $levelMove;

    /**
     * CustomerLevelChangedSystemEvent constructor.
     *
     * @param CustomerId  $customerId
     * @param LevelId     $levelId
     * @param null|string $levelName
     * @param null|bool   $levelMove
     */
    public function __construct(
        CustomerId $customerId,
        ?LevelId $levelId,
        ?string $levelName = null,
        ?bool $levelMove = null
    ) {
        parent::__construct($customerId);

        $this->levelId = $levelId;
        $this->levelName = $levelName;
        $this->levelMove = $levelMove;
    }

    /**
     * @return LevelId
     */
    public function getLevelId(): ?LevelId
    {
        return $this->levelId;
    }

    /**
     * @return null|string
     */
    public function getLevelName(): ?string
    {
        return $this->levelName;
    }

    /**
     * @return null|string
     */
    public function getLevelMove(): ?string
    {
        return (true === $this->levelMove) ? self::LEVEL_GOES_UP : self::LEVEL_GOES_DOWN;
    }
}
