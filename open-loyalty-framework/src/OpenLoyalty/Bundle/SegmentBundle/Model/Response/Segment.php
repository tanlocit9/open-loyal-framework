<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Model\Response;

/**
 * Class Segment.
 */
class Segment
{
    /**
     * @var string
     */
    private $segmentId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var SegmentPart[]
     */
    private $parts;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var int
     */
    private $customersCount;

    /**
     * Segment constructor.
     *
     * @param string        $segmentId
     * @param string        $name
     * @param string|null   $description
     * @param bool          $active
     * @param SegmentPart[] $parts
     * @param \DateTime     $createdAt
     * @param int           $customersCount
     */
    public function __construct(
            string $segmentId,
            string $name,
            ?string $description,
            bool $active,
            array $parts,
            \DateTime $createdAt,
            int $customersCount
    ) {
        $this->segmentId = $segmentId;
        $this->name = $name;
        $this->description = $description;
        $this->active = $active;
        $this->parts = $parts;
        $this->createdAt = $createdAt;
        $this->customersCount = $customersCount;
    }

    /**
     * @return string
     */
    public function getSegmentId(): string
    {
        return $this->segmentId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return SegmentPart[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getCustomersCount(): int
    {
        return $this->customersCount;
    }
}
