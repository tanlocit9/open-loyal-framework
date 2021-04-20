<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Model\Criteria;

use OpenLoyalty\Component\Segment\Domain\CriterionId;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use Assert\Assertion as Assert;

/**
 * Class LastPurchaseNDaysBefore.
 */
class LastPurchaseNDaysBefore extends Criterion
{
    /**
     * @var int
     */
    protected $days;

    public static function fromArray(array $data)
    {
        $criterion = new self(new CriterionId($data['criterionId']));
        $criterion->setDays($data['days']);

        return $criterion;
    }

    public static function validate(array $data)
    {
        parent::validate($data);
        Assert::keyIsset($data, 'days');
        Assert::notBlank($data, 'days');
        Assert::integer($data['days']);
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param int $days
     */
    public function setDays($days)
    {
        $this->days = $days;
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return [
            'days' => $this->getDays(),
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Criterion::TYPE_LAST_PURCHASE_N_DAYS_BEFORE;
    }
}
