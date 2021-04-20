<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\SystemEvent;

use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Infrastructure\Model\EvaluationResult;

/**
 * Class GeoEventOccurredSystemEvent.
 */
class GeoEventOccurredSystemEvent extends CustomEventOccurredSystemEvent
{
    /**
     * @var EvaluationResult[]
     */
    protected $evaluationResults;

    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @var string
     */
    protected $earningRuleId;

    /**
     * {@inheritdoc}
     *
     * @param float  $latitude
     * @param float  $longitude
     * @param string $earningRuleId
     */
    public function __construct(CustomerId $customerId, float $latitude, float $longitude, string $earningRuleId = null)
    {
        parent::__construct($customerId, '');
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->earningRuleId = $earningRuleId;
    }

    /**
     * @return EvaluationResult[]
     */
    public function getEvaluationResults(): array
    {
        return $this->evaluationResults;
    }

    /**
     * @param EvaluationResult[] $evaluationResults
     */
    public function setEvaluationResults(array $evaluationResults): void
    {
        $this->evaluationResults = $evaluationResults;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude(float $latitude): void
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
     * @param float $longitude
     */
    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    /**
     * @return string|null
     */
    public function getEarningRuleId(): ?string
    {
        return $this->earningRuleId;
    }

    /**
     * @param string|null $earningRuleId
     */
    public function setEarningRuleId(?string $earningRuleId): void
    {
        $this->earningRuleId = $earningRuleId;
    }
}
