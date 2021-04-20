<?php
/**
 * Copyright ÂŠ 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Assert\Assertion as Assert;

/**
 * Class EarningRuleQrcode.
 */
class EarningRuleQrcode extends EarningRule
{
    /**
     * @param string
     */
    protected $code;

    /**
     * @var float
     */
    protected $pointsAmount;

    /**
     * @var EarningRuleLimit
     */
    protected $limit;

    /**
     * {@inheritdoc}
     */
    public function setFromArray(array $earningRuleData = [])
    {
        parent::setFromArray($earningRuleData);

        if (isset($earningRuleData['code'])) {
            $this->code = $earningRuleData['code'];
        }
        if (isset($earningRuleData['pointsAmount'])) {
            $this->pointsAmount = $earningRuleData['pointsAmount'];
        }

        if (isset($earningRuleData['limit'])) {
            $this->limit = EarningRuleLimit::fromArray($earningRuleData['limit']);
        }
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @param float
     */
    public function setPointsAmount(float $pointsAmount)
    {
        $this->pointsAmount = $pointsAmount;
    }

    /**
     * @return float
     */
    public function getPointsAmount(): float
    {
        return (float) $this->pointsAmount;
    }

    public static function validateRequiredData(array $earningRuleData = [])
    {
        parent::validateRequiredData($earningRuleData);
        Assert::keyIsset($earningRuleData, 'code');
        Assert::keyIsset($earningRuleData, 'pointsAmount');

        Assert::notBlank($earningRuleData['code']);
        Assert::notBlank($earningRuleData['pointsAmount']);

        Assert::min($earningRuleData['pointsAmount'], 0);
        if (isset($earningRuleData['limit'])) {
            EarningRuleLimit::validateRequiredData($earningRuleData['limit']);
        }
    }

    /**
     * @return EarningRuleLimit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param EarningRuleLimit $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
}
