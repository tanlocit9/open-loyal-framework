<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class CustomerLevelWasRecalculated.
 */
class CustomerLevelWasRecalculated extends CustomerEvent
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * CustomerLevelWasRecalculated constructor.
     *
     * @param CustomerId $customerId
     * @param \DateTime  $date
     */
    public function __construct(CustomerId $customerId, \DateTime $date)
    {
        parent::__construct($customerId);
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(parent::serialize(), array(
            'date' => $this->date ? $this->date->getTimestamp() : null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $date = new \DateTime();
        if (isset($data['date'])) {
            $date->setTimestamp($data['date']);
        }

        return new self(
            new CustomerId($data['customerId']),
            $date
        );
    }
}
