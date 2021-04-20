<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Broadway\Repository\Repository;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountSystemEvents;
use OpenLoyalty\Component\Account\Domain\TransactionId;
use OpenLoyalty\Component\Customer\Domain\LevelId;
use OpenLoyalty\Component\Customer\Domain\PosId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetailsRepository;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\EarningRuleAlgorithmFactoryInterface;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\EarningRuleAlgorithmInterface;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\RuleEvaluationContext;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\RuleNameContext;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\RuleNameContextInterface;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableProvider;
use OpenLoyalty\Component\Account\Infrastructure\EarningRuleApplier;
use OpenLoyalty\Component\Account\Infrastructure\Model\EvaluationResult;
use OpenLoyalty\Component\Account\Infrastructure\Model\ReferralEvaluationResult;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Segment\Domain\ReadModel\SegmentedCustomers;
use OpenLoyalty\Component\Segment\Domain\ReadModel\SegmentedCustomersRepository;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\Transaction;

/**
 * Class OloyEarningRuleEvaluator.
 */
class OloyEarningRuleEvaluator implements EarningRuleApplier
{
    /**
     * @var EarningRuleQrcodeRepository
     */
    protected $earningRuleQrcodeRepository;

    /**
     * @var EarningRuleGeoRepository
     */
    protected $earningRuleGeoRepository;

    /**
     * @var EarningRuleRepository
     */
    protected $earningRuleRepository;

    /**
     * @var InvitationDetailsRepository
     */
    protected $invitationDetailsRepository;

    /**
     * @var EarningRuleAlgorithmFactoryInterface
     */
    protected $algorithmFactory;

    /**
     * @var SegmentedCustomersRepository
     */
    protected $segmentedCustomerElasticSearchRepository;

    /**
     * @var CustomerDetailsRepository
     */
    protected $customerDetailsRepository;

    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * @var StoppableProvider
     */
    private $stoppableProvider;

    /**
     * @var Repository
     */
    private $transactionRepository;

    /**
     * OloyEarningRuleEvaluator constructor.
     *
     * @param EarningRuleRepository                $earningRuleRepository
     * @param EarningRuleAlgorithmFactoryInterface $algorithmFactory
     * @param InvitationDetailsRepository          $invitationDetailsRepository
     * @param SegmentedCustomersRepository         $segmentedCustomerElasticSearchRepository
     * @param CustomerDetailsRepository            $customerDetailsRepository
     * @param SettingsManager                      $settingsManager
     * @param StoppableProvider                    $stoppableProvider
     * @param EarningRuleGeoRepository             $earningRuleGeoRepository
     * @param EarningRuleQrcodeRepository          $earningRuleQrcodeRepository
     * @param Repository                           $transactionRepository
     */
    public function __construct(
        EarningRuleRepository $earningRuleRepository,
        EarningRuleAlgorithmFactoryInterface $algorithmFactory,
        InvitationDetailsRepository $invitationDetailsRepository,
        SegmentedCustomersRepository $segmentedCustomerElasticSearchRepository,
        CustomerDetailsRepository $customerDetailsRepository,
        SettingsManager $settingsManager,
        StoppableProvider $stoppableProvider,
        EarningRuleGeoRepository $earningRuleGeoRepository,
        EarningRuleQrcodeRepository $earningRuleQrcodeRepository,
        Repository $transactionRepository
    ) {
        $this->earningRuleRepository = $earningRuleRepository;
        $this->algorithmFactory = $algorithmFactory;
        $this->segmentedCustomerElasticSearchRepository = $segmentedCustomerElasticSearchRepository;
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->invitationDetailsRepository = $invitationDetailsRepository;
        $this->settingsManager = $settingsManager;
        $this->stoppableProvider = $stoppableProvider;
        $this->earningRuleGeoRepository = $earningRuleGeoRepository;
        $this->earningRuleQrcodeRepository = $earningRuleQrcodeRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param TransactionDetails|TransactionId $transaction
     *
     * @return Transaction
     */
    protected function getTransactionObject($transaction): Transaction
    {
        if ($transaction instanceof TransactionId) {
            /** @var Transaction $transaction */
            $transaction = $this->transactionRepository->load((string) $transaction);
        }

        if ($transaction instanceof TransactionDetails) {
            /** @var Transaction $transaction */
            $transaction = $this->transactionRepository->load((string) $transaction->getTransactionId());
        }

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     * @param string      $customerId
     *
     * @return array
     */
    protected function getEarningRulesAlgorithms(Transaction $transaction, string $customerId)
    {
        $customerData = $this->getCustomerDetails($customerId);

        $earningRules = $this->earningRuleRepository->findAllActiveEventRulesBySegmentsAndLevels(
            $transaction->getPurchaseDate(),
            $customerData['segments'],
            $customerData['level'],
            $transaction->getPosId()
        );

        $result = [];

        foreach ($earningRules as $earningRule) {
            // ignore event rules (supported by call method)
            if ($earningRule instanceof EventEarningRule
                || $earningRule instanceof CustomEventEarningRule
                || $earningRule instanceof EarningRuleGeo
                || $earningRule instanceof EarningRuleQrcode
                || $earningRule instanceof ReferralEarningRule
            ) {
                continue;
            }

            /** @var EarningRuleAlgorithmInterface $algorithm */
            $algorithm = $this->algorithmFactory->getAlgorithm($earningRule);
            $result[] = [
                $earningRule,
                $algorithm,
            ];
        }

        usort(
            $result,
            function ($x, $y) {
                return $x[1]->getPriority() - $y[1]->getPriority();
            }
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateTransaction(Transaction $transaction, string $customerId, RuleEvaluationContext $context = null): float
    {
        $customerData = $this->getCustomerDetails($customerId);
        if (null !== $customerData['status'] && !in_array($customerData['status'], $this->getCustomerEarningStatuses())) {
            return 0;
        }

        $earningRulesItems = $this->getEarningRulesAlgorithms($transaction, $customerId);

        if (null === $context) {
            $context = new RuleEvaluationContext($transaction, $customerId);
        }

        foreach ($earningRulesItems as $earningRuleItem) {
            /** @var EarningRule $earningRule */
            $earningRule = $earningRuleItem[0];
            /** @var EarningRuleAlgorithmInterface $algorithm */
            $algorithm = $earningRuleItem[1];

            $executed = $algorithm->evaluate($context, $earningRule);

            if ($executed && $this->stoppableProvider->isStoppable($earningRule) && $earningRule->isLastExecutedRule()) {
                break;
            }
        }

        return round((float) array_sum($context->getProducts()), 2);
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateTransactionWithComment(TransactionId $transaction, string $customerId): array
    {
        $transaction = $this->getTransactionObject($transaction);

        if (!$transaction) {
            return [
                'points' => 0,
                'comment' => null,
            ];
        }

        $context = new RuleEvaluationContext($transaction, $customerId);
        $points = $this->evaluateTransaction($transaction, $customerId, $context);

        return [
            'points' => $points,
            'comment' => $context->getEarningRuleNames(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateEvent(string $eventName, ?string $customerId, RuleNameContextInterface $context = null): float
    {
        $points = 0;

        $customerData = $this->getCustomerDetails($customerId);
        // exclude new inactive accounts which always have "new" status until they become activated
        if (AccountSystemEvents::ACCOUNT_CREATED !== $eventName
            && null !== $customerData['status']
            && !in_array($customerData['status'], $this->getCustomerEarningStatuses())
        ) {
            return 0;
        }

        $earningRules = $this->earningRuleRepository->findAllActiveEventRules(
            $eventName,
            $customerData['segments'],
            $customerData['level'],
            null,
            $customerData['pos']
        );

        /** @var EventEarningRule $earningRule */
        foreach ($earningRules as $earningRule) {
            if ($earningRule->getPointsAmount() > $points) {
                $points = $earningRule->getPointsAmount();
                if (null !== $context) {
                    $context->addEarningRuleName($earningRule->getEarningRuleId(), $earningRule->getName());
                }
            }
        }

        return round((float) $points, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateEventWithContext(string $eventName, ?string $customerId): array
    {
        $context = new RuleNameContext();
        $points = $this->evaluateEvent($eventName, $customerId, $context);

        return [
            'points' => $points,
            'comment' => $context->getEarningRuleNames(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateGeoEvent(float $latitude, float $longitude, string $customerId, ?string $earningRuleId): array
    {
        /** @var EvaluationResult[] $result */
        $result = [];

        /** @var array $customerData */
        $customerData = $this->getCustomerDetails($customerId);
        if (null !== $customerData['status'] && !in_array($customerData['status'], $this->getCustomerEarningStatuses())) {
            return $result;
        }

        $earningGeoRules = $this->earningRuleGeoRepository->findGeoRules(
            $earningRuleId,
            $customerData['segments'],
            $customerData['level'],
            null,
            $customerData['pos']
        );

        foreach ($earningGeoRules as $earningGeoRule) {
            /** @var EarningRuleGeo $earningGeoRule */
            if ($earningGeoRule->isActive()) {
                $distance = $earningGeoRule->getDistance($latitude, $longitude);
                if ($earningGeoRule->getRadius() >= $distance) {
                    $result[] = new EvaluationResult(
                        (string) $earningGeoRule->getEarningRuleId(),
                        $earningGeoRule->getPointsAmount(),
                        $earningGeoRule->getName()
                    );
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateCustomEvent(string $eventName, string $customerId): EvaluationResult
    {
        /** @var EvaluationResult $result */
        $result = new EvaluationResult('', 0.0);

        /** @var array $customerData */
        $customerData = $this->getCustomerDetails($customerId);
        if (null !== $customerData['status'] && !in_array($customerData['status'], $this->getCustomerEarningStatuses())) {
            return $result;
        }

        /** @var CustomEventEarningRule[] $earningRules */
        $earningRules = $this->earningRuleRepository->findByCustomEventName(
            $eventName,
            $customerData['segments'],
            $customerData['level'],
            null,
            $customerData['pos']
        );

        if (!$earningRules) {
            return $result;
        }

        if (null !== $customerData['status'] && !in_array($customerData['status'], $this->getCustomerEarningStatuses())) {
            return $result;
        }

        foreach ($earningRules as $earningRule) {
            if (null === $result || $earningRule->getPointsAmount() > $result->getPoints()) {
                $result = new EvaluationResult(
                    (string) $earningRule->getEarningRuleId(),
                    $earningRule->getPointsAmount(),
                    $earningRule->getName()
                );
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateReferralEvent(string $eventName, string $customerId): array
    {
        /** @var ReferralEvaluationResult[] $results */
        $results = [];

        /** @var array $customerData */
        $customerData = $this->getCustomerDetails($customerId);

        $invitation = $this->invitationDetailsRepository->findOneByRecipientId(new \OpenLoyalty\Component\Customer\Domain\CustomerId($customerId));

        if (!$invitation) {
            return $results;
        }

        /** @var ReferralEarningRule[] $earningRules */
        $earningRules = $this->earningRuleRepository->findReferralByEventName(
            $eventName,
            $customerData['segments'],
            $customerData['level'],
            null,
            $customerData['pos']
        );
        if (!$earningRules) {
            return $results;
        }

        foreach ($earningRules as $earningRule) {
            if (!isset($results[$earningRule->getRewardType()]) || $earningRule->getPointsAmount() > $results[$earningRule->getRewardType()]->getPoints()) {
                $results[$earningRule->getRewardType()] = new ReferralEvaluationResult(
                    (string) $earningRule->getEarningRuleId(),
                    $earningRule->getPointsAmount(),
                    $earningRule->getRewardType(),
                    $invitation,
                    $earningRule->getName()
                );
            }
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateQrcodeEvent(string $code, string $customerId, ?string $earningRuleId): array
    {
        /** @var EvaluationResult[] $result */
        $result = [];

        /** @var array $customerData */
        $customerData = $this->getCustomerDetails($customerId);

        $earningQrcodeRules = $this->earningRuleQrcodeRepository->findAllActiveQrcodeRules(
            $code,
            $earningRuleId,
            $customerData['segments'],
            $customerData['level'],
            null,
            $customerData['pos']
        );

        /* @var EarningRuleQrcode $earningQrcodeRule */
        foreach ($earningQrcodeRules as $earningQrcodeRule) {
            $result[] = new EvaluationResult(
                (string) $earningQrcodeRule->getEarningRuleId(),
                $earningQrcodeRule->getPointsAmount(),
                $earningQrcodeRule->getName()
            );
        }

        return $result;
    }

    /**
     * Get customer level and segments data from transaction.
     *
     * @param string customerId
     *
     * @return array
     */
    protected function getCustomerDetails(string $customerId): array
    {
        $result = [
            'level' => null,
            'status' => null,
            'segments' => [],
            'pos' => null,
        ];

        if ($customerId) {
            $customerDetails = $this->customerDetailsRepository->findOneByCriteria(['id' => $customerId], 1);
            $levelId = $this->getCustomerLevelById($customerDetails);
            $status = $this->getCustomerStatusById($customerDetails);
            $pos = $this->getCustomerPos($customerDetails);

            $arrayOfSegments = $this->getCustomerSegmentsById($customerId);

            $result = [
                'level' => $levelId,
                'status' => $status,
                'segments' => $arrayOfSegments,
                'pos' => $pos,
            ];
        }

        return $result;
    }

    /**
     * @param CustomerDetails[] $customerDetails
     *
     * @return null|PosId
     */
    public function getCustomerPos(array $customerDetails): ?PosId
    {
        if (!$customerDetails) {
            return null;
        }

        $pos = array_map(
            function (CustomerDetails $element) {
                return $element->getPosId();
            },
            $customerDetails
        );

        return isset($pos[0]) ? $pos[0] : null;
    }

    /**
     * Get customers segments.
     *
     * @param string $customerId
     *
     * @return array
     */
    protected function getCustomerSegmentsById(string $customerId): array
    {
        $segments = [];

        $customerDetails = $this->segmentedCustomerElasticSearchRepository
            ->findByParameters(
                ['customerId' => $customerId],
                true
            );

        if ($customerDetails) {
            $segments = array_map(
                function (SegmentedCustomers $element) {
                    return $element->getSegmentId();
                },
                $customerDetails
            );
        }

        return $segments;
    }

    /**
     * Get customers level.
     *
     * @param CustomerDetails[] $customerDetails
     *
     * @return LevelId|null
     */
    protected function getCustomerLevelById(array $customerDetails): ?LevelId
    {
        if (!$customerDetails) {
            return null;
        }

        $levels = array_map(
            function (CustomerDetails $element) {
                return $element->getLevelId();
            },
            $customerDetails
        );

        return isset($levels[0]) ? $levels[0] : null;
    }

    /**
     * Get customers status.
     *
     * @param CustomerDetails[] $customerDetails
     *
     * @return string|null
     */
    protected function getCustomerStatusById(array $customerDetails): ?string
    {
        if (!$customerDetails) {
            return null;
        }

        $statuses = array_map(
            function (CustomerDetails $element) {
                return (null !== $element->getStatus()) ? $element->getStatus()->getType() : null;
            },
            $customerDetails
        );

        return isset($statuses[0]) ? $statuses[0] : null;
    }

    /**
     * @return array
     */
    protected function getCustomerEarningStatuses(): array
    {
        $customerStatusesEarning = $this->settingsManager->getSettingByKey('customerStatusesEarning');
        if ($customerStatusesEarning) {
            return $customerStatusesEarning->getValue();
        }

        return [];
    }
}
