<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\SellerId;

/**
 * Class SellerWasAssignedToCustomer.
 */
class SellerWasAssignedToCustomer extends CustomerEvent
{
    /**
     * @var SellerId
     */
    protected $sellerId;

    /**
     * @var \DateTime
     */
    protected $updateAt;

    public function __construct(CustomerId $customerId, SellerId $sellerId)
    {
        parent::__construct($customerId);
        $this->sellerId = $sellerId;
        $this->updateAt = new \DateTime();
        $this->updateAt->setTimestamp(time());
    }

    public function serialize(): array
    {
        return array_merge(parent::serialize(), [
            'sellerId' => $this->sellerId->__toString(),
            'updatedAt' => $this->updateAt ? $this->updateAt->getTimestamp() : null,
        ]);
    }

    /**
     * @param array $data
     *
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        $event = new self(new CustomerId($data['customerId']), new SellerId($data['sellerId']));
        if (isset($data['updatedAt'])) {
            $date = new \DateTime();
            $date->setTimestamp($data['updatedAt']);
            $event->setUpdateAt($date);
        }

        return $event;
    }

    /**
     * @return SellerId
     */
    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * @param \DateTime $updateAt
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;
    }
}
