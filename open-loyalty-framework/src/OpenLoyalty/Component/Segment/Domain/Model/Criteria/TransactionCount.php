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
 * Class TransactionCount.
 */
class TransactionCount extends Criterion
{
    /**
     * @var int
     */
    protected $min;

    /**
     * @var int
     */
    protected $max;

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    public static function fromArray(array $data)
    {
        $criterion = new self(new CriterionId($data['criterionId']));
        $criterion->setMin($data['min']);
        $criterion->setMax($data['max']);

        return $criterion;
    }

    public static function validate(array $data)
    {
        parent::validate($data);
        Assert::keyIsset($data, 'min');
        Assert::keyIsset($data, 'max');
        Assert::notBlank($data, 'min');
        Assert::notBlank($data, 'max');
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return [
            'min' => $this->getMin(),
            'max' => $this->getMax(),
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Criterion::TYPE_TRANSACTION_COUNT;
    }
}
