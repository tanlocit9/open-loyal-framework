<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\LevelId;

/**
 * Class CustomerWasMovedToLevel.
 */
class CustomerWasMovedToLevel extends CustomerEvent
{
    /**
     * @var LevelId|null
     */
    protected $levelId = null;

    /**
     * @var LevelId|null
     */
    private $oldLevelId;

    /**
     * @var \DateTime
     */
    protected $updateAt;

    /**
     * @var bool
     */
    protected $manually = false;

    /**
     * @var bool
     */
    protected $removeLevelManually = false;

    /**
     * CustomerWasMovedToLevel constructor.
     *
     * @param CustomerId   $customerId
     * @param LevelId|null $levelId
     * @param LevelId|null $oldLevelId
     * @param bool         $manually
     * @param bool         $removeLevelManually
     */
    public function __construct(
        CustomerId $customerId,
        LevelId $levelId = null,
        LevelId $oldLevelId = null,
        $manually = false,
        bool $removeLevelManually = false
    ) {
        parent::__construct($customerId);
        $this->levelId = $levelId;
        $this->oldLevelId = $oldLevelId;
        $this->updateAt = new \DateTime();
        $this->updateAt->setTimestamp(time());
        $this->manually = $manually;
        $this->removeLevelManually = $removeLevelManually;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(parent::serialize(), [
            'levelId' => $this->levelId ? (string) $this->levelId : null,
            'oldLevelId' => $this->oldLevelId ? (string) $this->oldLevelId : null,
            'updatedAt' => $this->updateAt ? $this->updateAt->getTimestamp() : null,
            'manually' => $this->manually,
            'removeLevelManually' => $this->removeLevelManually,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $event = new self(
            new CustomerId($data['customerId']),
            $data['levelId'] ? new LevelId($data['levelId']) : null,
            $data['oldLevelId'] ? new LevelId($data['oldLevelId']) : null,
            $data['manually'],
            $data['removeLevelManually'] ?? false
        );
        if (isset($data['updatedAt'])) {
            $date = new \DateTime();
            $date->setTimestamp($data['updatedAt']);
            $event->setUpdateAt($date);
        }

        return $event;
    }

    /**
     * @return LevelId|null
     */
    public function getLevelId(): ?LevelId
    {
        return $this->levelId;
    }

    /**
     * @return LevelId|null
     */
    public function getOldLevelId(): ?LevelId
    {
        return $this->oldLevelId;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateAt(): \DateTime
    {
        return $this->updateAt;
    }

    /**
     * @param \DateTime $updateAt
     */
    public function setUpdateAt(\DateTime $updateAt): void
    {
        $this->updateAt = $updateAt;
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
     * @param bool $removeLevelManually
     */
    public function setRemoveLevelManually(bool $removeLevelManually): void
    {
        $this->removeLevelManually = $removeLevelManually;
    }
}
