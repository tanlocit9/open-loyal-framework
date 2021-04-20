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
 * Class PurchaseInPeriod.
 */
class PurchaseInPeriod extends Criterion
{
    /**
     * @var \DateTime
     */
    protected $fromDate;

    /**
     * @var \DateTime
     */
    protected $toDate;

    /**
     * @return \DateTime
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * @param \DateTime $fromDate
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;
    }

    /**
     * @return \DateTime
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * @param \DateTime $toDate
     */
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;
    }

    public static function fromArray(array $data)
    {
        $criterion = new self(new CriterionId($data['criterionId']));
        $criterion->setFromDate($data['fromDate']);
        $criterion->setToDate($data['toDate']);

        return $criterion;
    }

    public static function validate(array $data)
    {
        parent::validate($data);
        Assert::keyIsset($data, 'fromDate');
        Assert::keyIsset($data, 'toDate');
        Assert::notBlank($data, 'fromDate');
        Assert::notBlank($data, 'toDate');
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return [
            'fromDate' => $this->getFromDate(),
            'toDate' => $this->getToDate(),
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Criterion::TYPE_PURCHASE_PERIOD;
    }
}
