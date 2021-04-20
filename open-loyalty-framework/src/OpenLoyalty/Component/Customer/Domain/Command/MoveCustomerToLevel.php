<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\LevelId;

/**
 * Class MoveCustomerToLevel.
 */
class MoveCustomerToLevel extends CustomerCommand
{
    /**
     * @var LevelId
     */
    protected $levelId;

    /**
     * @var string
     */
    protected $levelName;

    /**
     * @var bool
     */
    protected $manually = false;

    /**
     * @var bool
     */
    protected $removeLevelManually = false;

    /**
     * @var \DateTime
     */
    protected $dateTime;

    /**
     * MoveCustomerToLevel constructor.
     *
     * @param CustomerId     $customerId
     * @param null|LevelId   $levelId
     * @param null|string    $levelName
     * @param bool           $manually
     * @param bool           $removeLevelManually
     * @param \DateTime|null $dateTime
     */
    public function __construct(
        CustomerId $customerId,
        ?LevelId $levelId = null,
        ?string $levelName = null,
        bool $manually = false,
        bool $removeLevelManually = false,
        \DateTime $dateTime = null
    ) {
        parent::__construct($customerId);

        $this->levelId = $levelId;
        $this->levelName = $levelName;
        $this->manually = $manually;
        $this->removeLevelManually = $removeLevelManually;
        $this->dateTime = $dateTime ?: new \DateTime();
    }

    /**
     * @return null|LevelId
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
     * @return bool
     */
    public function isManually(): bool
    {
        return $this->manually;
    }

    /**
     * @return bool
     */
    public function isRemoveLevelManually(): bool
    {
        return $this->removeLevelManually;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }
}
