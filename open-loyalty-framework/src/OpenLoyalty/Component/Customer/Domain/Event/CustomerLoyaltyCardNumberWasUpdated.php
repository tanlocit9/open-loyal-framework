<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class CustomerLoyaltyCardNumberWasUpdated.
 */
class CustomerLoyaltyCardNumberWasUpdated extends CustomerEvent
{
    protected $cardNumber;

    /**
     * @var \DateTime
     */
    protected $updateAt;

    /**
     * CustomerAddressWasUpdated constructor.
     *
     * @param CustomerId $customerId
     * @param $cardNumber
     */
    public function __construct(CustomerId $customerId, $cardNumber)
    {
        parent::__construct($customerId);
        $this->cardNumber = $cardNumber;
        $this->updateAt = new \DateTime();
        $this->updateAt->setTimestamp(time());
    }

    /**
     * @return mixed
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    public function serialize(): array
    {
        return array_merge(parent::serialize(), array(
            'cardNumber' => $this->cardNumber,
            'updatedAt' => $this->updateAt ? $this->updateAt->getTimestamp() : null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $event = new self(
            new CustomerId($data['customerId']),
            $data['cardNumber']
        );

        if (isset($data['updatedAt'])) {
            $date = new \DateTime();
            $date->setTimestamp($data['updatedAt']);
            $event->setUpdateAt($date);
        }

        return $event;
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
