<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Model;

use Broadway\Serializer\Serializable;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use Assert\Assertion as Assert;

/**
 * Class PointsTransfer.
 */
abstract class PointsTransfer implements Serializable
{
    const ISSUER_ADMIN = 'admin';
    const ISSUER_SELLER = 'seller';
    const ISSUER_SYSTEM = 'system';
    const ISSUER_API = 'api';
    const ISSUER_INTERNAL = 'internal';

    const TYPE_SYSTEM = 'system';
    const TYPE_P2P = 'p2p';

    /**
     * @var PointsTransferId
     */
    protected $id;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var float
     */
    protected $value;

    /**
     * @var bool
     */
    protected $canceled = false;

    /**
     * @var string
     */
    protected $issuer = self::ISSUER_SYSTEM;

    /**
     * PointsTransfer constructor.
     *
     * @param PointsTransferId $id
     * @param float            $value
     * @param \DateTime        $createdAt
     * @param bool             $canceled
     * @param string|null      $comment
     * @param string           $issuer
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        PointsTransferId $id,
        float $value,
        \DateTime $createdAt = null,
        bool $canceled = false,
        $comment = null,
        $issuer = self::ISSUER_SYSTEM
    ) {
        $this->id = $id;
        Assert::notBlank($value);
        Assert::numeric($value);
        Assert::greaterThan($value, 0);

        $this->value = $value;

        if ($createdAt) {
            $this->createdAt = $createdAt;
        } else {
            $this->createdAt = new \DateTime();
            $this->createdAt->setTimestamp(time());
        }

        $this->comment = $comment;
        $this->canceled = $canceled;
        $this->issuer = $issuer;
    }

    /**
     * @return PointsTransferId
     */
    public function getId(): PointsTransferId
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return (float) $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'id' => $this->id->__toString(),
            'value' => $this->value,
            'createdAt' => $this->createdAt->getTimestamp(),
            'canceled' => $this->canceled,
            'comment' => $this->comment,
            'issuer' => $this->issuer,
        ];
    }

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * @return string
     */
    abstract public function getType(): string;
}
