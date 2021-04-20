<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Model;

/**
 * Class EarningGeoRule.
 */
class EarningGeoRule
{
    /**
     * @var float|null
     */
    protected $latitude;

    /**
     * @var float|null
     */
    protected $longitude;

    /**
     * @var string|null
     */
    protected $earningRuleId;

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param $longitude
     */
    public function setLongitude(float $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return string|null
     */
    public function getEarningRuleId(): ?string
    {
        return $this->earningRuleId;
    }

    /**
     * @param $earningRuleId
     */
    public function setEarningRuleId(?string $earningRuleId)
    {
        $this->earningRuleId = $earningRuleId;
    }
}
