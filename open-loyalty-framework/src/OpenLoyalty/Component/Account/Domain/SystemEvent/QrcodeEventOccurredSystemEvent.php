<?php
/**
 * Copyright ÂŠ 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\SystemEvent;

use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Infrastructure\Model\EvaluationResult;

/**
 * Class QrcodeEventOccurredSystemEvent.
 */
class QrcodeEventOccurredSystemEvent extends CustomEventOccurredSystemEvent
{
    /**
     * @var EvaluationResult[]
     */
    protected $evaluationResults;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $earningRuleId;

    /**
     * {@inheritdoc}
     *
     * @param float $code
     */
    public function __construct(CustomerId $customerId, $code, $earningRuleId = null)
    {
        parent::__construct($customerId, '');
        $this->code = $code;
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
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
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
