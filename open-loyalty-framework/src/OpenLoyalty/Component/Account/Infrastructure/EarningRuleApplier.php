<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Infrastructure;

use OpenLoyalty\Component\Account\Domain\TransactionId;
use OpenLoyalty\Component\Account\Infrastructure\Model\EvaluationResult;
use OpenLoyalty\Component\Account\Infrastructure\Model\ReferralEvaluationResult;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\RuleNameContextInterface;
use OpenLoyalty\Component\Transaction\Domain\Transaction;

interface EarningRuleApplier
{
    /**
     * Return number of points for this transaction.
     *
     * @param Transaction $transaction
     * @param string      $customerId
     *
     * @return float
     */
    public function evaluateTransaction(Transaction $transaction, string $customerId): float;

    /**
     * @param TransactionId $transaction
     * @param string        $customerId
     *
     * @return array
     */
    public function evaluateTransactionWithComment(TransactionId $transaction, string $customerId): array;

    /**
     * Return number of points for this event.
     *
     * @param string                        $eventName
     * @param string|null                   $customerId
     * @param RuleNameContextInterface|null $context
     *
     * @return float
     */
    public function evaluateEvent(string $eventName, ?string $customerId, ?RuleNameContextInterface $context = null): float;

    /**
     * Return number of points for this event.
     *
     * @param string      $eventName
     * @param string|null $customerId
     *
     * @return array
     */
    public function evaluateEventWithContext(string $eventName, ?string $customerId): array;

    /**
     * Return number of points for this custom event.
     *
     * @param string $eventName
     * @param string $customerId
     *
     * @return EvaluationResult
     */
    public function evaluateCustomEvent(string $eventName, string $customerId): EvaluationResult;

    /**
     * @param string $eventName
     * @param string $customerId
     *
     * @return ReferralEvaluationResult[]
     */
    public function evaluateReferralEvent(string $eventName, string $customerId): array;

    /**
     * @param float       $latitude
     * @param float       $longitude
     * @param string      $customerId
     * @param string|null $earningRuleId
     *
     * @return EvaluationResult[]
     */
    public function evaluateGeoEvent(float $latitude, float $longitude, string $customerId, ?string $earningRuleId): array;

    /**
     * @param string      $code
     * @param string      $customerId
     * @param string|null $earningRuleId
     *
     * @return EvaluationResult[]
     */
    public function evaluateQrcodeEvent(string $code, string $customerId, ?string $earningRuleId): array;
}
