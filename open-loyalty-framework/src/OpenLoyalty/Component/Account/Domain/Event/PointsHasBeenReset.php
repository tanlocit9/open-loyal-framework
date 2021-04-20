<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Event;

use OpenLoyalty\Component\Account\Domain\AccountId;

/**
 * Class PointsHasBeenReset.
 */
class PointsHasBeenReset extends AccountEvent
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * PointsHasBeenReset constructor.
     *
     * @param AccountId $accountId
     * @param \DateTime $date
     */
    public function __construct(AccountId $accountId, \DateTime $date)
    {
        parent::__construct($accountId);
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
        return array_merge(
            parent::serialize(),
            [
                'date' => $this->date ? $this->date->getTimestamp() : null,
            ]
        );
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

        return new self(new AccountId($data['accountId']), $date);
    }
}
