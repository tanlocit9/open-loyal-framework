<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;
use OpenLoyalty\Component\Segment\Domain\CustomerId;
use OpenLoyalty\Component\Segment\Domain\SegmentId;

/**
 * Class SegmentedCustomers.
 */
class SegmentedCustomers implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var SegmentId
     */
    protected $segmentId;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var string
     */
    protected $segmentName;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $email;

    /**
     * SegmentedCustomers constructor.
     *
     * @param SegmentId  $segmentId
     * @param CustomerId $customerId
     * @param string     $segmentName
     */
    public function __construct(SegmentId $segmentId, CustomerId $customerId, $segmentName = null)
    {
        $this->segmentId = $segmentId;
        $this->customerId = $customerId;
        $this->segmentName = $segmentName;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->segmentId->__toString().'_'.$this->customerId->__toString();
    }

    /**
     * @param array $data
     *
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        $tmp = new self(new SegmentId($data['segmentId']), new CustomerId($data['customerId']), $data['segmentName']);
        if (isset($data['firstName'])) {
            $tmp->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $tmp->setLastName($data['lastName']);
        }
        if (isset($data['phone'])) {
            $tmp->setPhone($data['phone']);
        }
        if (isset($data['email'])) {
            $tmp->setEmail($data['email']);
        }

        return $tmp;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'segmentId' => $this->segmentId->__toString(),
            'customerId' => $this->customerId->__toString(),
            'segmentName' => $this->segmentName,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }

    /**
     * @return SegmentId
     */
    public function getSegmentId()
    {
        return $this->segmentId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getSegmentName()
    {
        return $this->segmentName;
    }

    /**
     * @param string $segmentName
     */
    public function setSegmentName($segmentName)
    {
        $this->segmentName = $segmentName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
}
