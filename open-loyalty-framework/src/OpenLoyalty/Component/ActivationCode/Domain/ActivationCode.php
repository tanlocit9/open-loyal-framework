<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\ActivationCode\Domain;

use Assert\Assertion as Assert;

/**
 * Class ActivationCode.
 */
class ActivationCode
{
    /**
     * @var ActivationCodeId
     */
    protected $activationCodeId;

    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var string
     */
    protected $objectId;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * ActivationCode constructor.
     *
     * @param ActivationCodeId $activationCodeId
     * @param string           $objectType
     * @param string           $objectId
     * @param string           $code
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        ActivationCodeId $activationCodeId,
        string $objectType,
        string $objectId,
        string $code
    ) {
        Assert::uuid($activationCodeId->__toString());
        Assert::notEmpty($objectType);
        Assert::notEmpty($objectId);
        Assert::notEmpty($code);

        $this->activationCodeId = $activationCodeId;
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->code = $code;
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @param ActivationCodeId $id
     * @param array            $data
     *
     * @return ActivationCode
     *
     * @throws \Assert\AssertionFailedException
     */
    public static function create(ActivationCodeId $id, array $data)
    {
        return new self(
            $id,
            $data['objectType'],
            $data['objectId'],
            $data['code']
        );
    }

    /**
     * @return activationCodeId
     */
    public function getactivationCodeId(): ActivationCodeId
    {
        return $this->activationCodeId;
    }

    /**
     * @return string
     */
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType(string $objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @return string
     */
    public function getObjectId(): string
    {
        return $this->objectId;
    }

    /**
     * @param string $objectId
     */
    public function setObjectId(string $objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
