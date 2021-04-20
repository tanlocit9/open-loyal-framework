<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Model\Criteria;

use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use OpenLoyalty\Component\Segment\Domain\CriterionId;
use Assert\Assertion as Assert;

/**
 * Class TransactionAmount.
 */
class TransactionAmount extends Criterion
{
    /**
     * @var float
     */
    protected $fromAmount;

    /**
     * @var float
     */
    protected $toAmount;

    /**
     * @return float
     */
    public function getFromAmount()
    {
        return $this->fromAmount;
    }

    /**
     * @param float $fromAmount
     */
    public function setFromAmount($fromAmount)
    {
        $this->fromAmount = $fromAmount;
    }

    /**
     * @return float
     */
    public function getToAmount()
    {
        return $this->toAmount;
    }

    /**
     * @param float $toAmount
     */
    public function setToAmount($toAmount)
    {
        $this->toAmount = $toAmount;
    }

    public static function fromArray(array $data)
    {
        $criterion = new self(new CriterionId($data['criterionId']));
        $criterion->setFromAmount($data['fromAmount']);
        $criterion->setToAmount($data['toAmount']);

        return $criterion;
    }

    public static function validate(array $data)
    {
        parent::validate($data);
        Assert::keyIsset($data, 'fromAmount');
        Assert::keyIsset($data, 'toAmount');
        Assert::notBlank($data, 'fromAmount');
        Assert::notBlank($data, 'toAmount');
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return [
            'fromAmount' => round($this->getFromAmount(), 0),
            'toAmount' => round($this->getToAmount(), 0),
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Criterion::TYPE_TRANSACTION_AMOUNT;
    }
}
