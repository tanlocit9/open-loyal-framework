<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Assert\Assertion as Assert;

/**
 * Class EarningRuleGeo.
 */
class EarningRuleGeo extends EarningRule
{
    /**
     * @param float
     */
    protected $latitude;

    /**
     * @param float
     */
    protected $longitude;

    /**
     * @param float
     */
    protected $radius;

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
        if (isset($earningRuleData['latitude'])) {
            $this->latitude = $earningRuleData['latitude'];
        }
        if (isset($earningRuleData['longitude'])) {
            $this->longitude = $earningRuleData['longitude'];
        }
        if (isset($earningRuleData['radius'])) {
            $this->radius = $earningRuleData['radius'];
        }
        if (isset($earningRuleData['pointsAmount'])) {
            $this->pointsAmount = $earningRuleData['pointsAmount'];
        }
        if (isset($earningRuleData['limit'])) {
            $this->limit = EarningRuleLimit::fromArray($earningRuleData['limit']);
        }
        parent::setFromArray($earningRuleData);
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float
     */
    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float
     */
    public function setLongitude(float $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return float
     */
    public function getRadius(): float
    {
        return $this->radius;
    }

    /**
     * @param float
     */
    public function setRadius(float $radius)
    {
        $this->radius = $radius;
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

    /**
     * @return EarningRuleLimit
     */
    public function getLimit(): EarningRuleLimit
    {
        return $this->limit;
    }

    /**
     * @param EarningRuleLimit $limit
     */
    public function setLimit(EarningRuleLimit $limit): void
    {
        $this->limit = $limit;
    }

    public static function validateRequiredData(array $earningRuleData = [])
    {
        parent::validateRequiredData($earningRuleData);
        Assert::keyIsset($earningRuleData, 'radius');
        Assert::keyIsset($earningRuleData, 'latitude');
        Assert::keyIsset($earningRuleData, 'longitude');
        Assert::keyIsset($earningRuleData, 'pointsAmount');

        Assert::notBlank($earningRuleData['radius']);
        Assert::notBlank($earningRuleData['latitude']);
        Assert::notBlank($earningRuleData['longitude']);
        Assert::notBlank($earningRuleData['pointsAmount']);

        Assert::numeric($earningRuleData['radius']);
        Assert::numeric($earningRuleData['latitude']);
        Assert::numeric($earningRuleData['longitude']);

        Assert::min($earningRuleData['radius'], 0);
        Assert::min($earningRuleData['pointsAmount'], 0);
    }

    /**
     * Get distance in kilometers from a customer.
     *
     * @param float $customerLatitude
     * @param float $customerLongitude
     *
     * @return float
     */
    public function getDistance(float $customerLatitude, float $customerLongitude): float
    {
        $theta = $customerLongitude - $this->getLongitude();
        $dist = sin(deg2rad($customerLatitude)) * sin(deg2rad($this->getLatitude())) + cos(deg2rad($customerLatitude)) * cos(deg2rad($this->getLatitude())) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $val = $dist * 60 * 1.1515;

        // convert miles into meters
        $return = round($val * 1609.344, 2);

        // protect division by 0
        if ($return > 0) {
            // convert meters to the kilometers
            $return /= 1000;
        }

        return $return;
    }
}
