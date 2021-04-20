<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\SystemEvent;

use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Infrastructure\Model\EvaluationResult;

/**
 * Class CustomEventOccurredSystemEvent.
 */
class CustomEventOccurredSystemEvent
{
    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var string
     */
    protected $eventName;

    /**
     * @var EvaluationResult|null
     */
    protected $evaluationResult;

    /**
     * CustomEventOccurredSystemEvent constructor.
     *
     * @param CustomerId $customerId
     * @param            $eventName
     */
    public function __construct(CustomerId $customerId, $eventName)
    {
        $this->customerId = $customerId;
        $this->eventName = $eventName;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @param CustomerId $customerId
     */
    public function setCustomerId(CustomerId $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     */
    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }

    /**
     * @return EvaluationResult|null
     */
    public function getEvaluationResult(): ?EvaluationResult
    {
        return $this->evaluationResult;
    }

    /**
     * @param EvaluationResult $evaluationResult
     */
    public function setEvaluationResult(EvaluationResult $evaluationResult): void
    {
        $this->evaluationResult = $evaluationResult;
    }
}
