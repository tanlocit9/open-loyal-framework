<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\ReadModel\Repository;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\PointsBundle\Service\PointsTransfersManager;
use OpenLoyalty\Component\Account\Domain\Command\AddPoints;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountCreatedSystemEvent;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountSystemEvents;
use OpenLoyalty\Component\Account\Domain\SystemEvent\CustomEventOccurredSystemEvent;
use OpenLoyalty\Component\Account\Domain\SystemEvent\GeoEventOccurredSystemEvent;
use OpenLoyalty\Component\Account\Infrastructure\Exception\EarningRuleLimitExceededException;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerAttachedToInvitationSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerLoggedInSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\NewsletterSubscriptionSystemEvent;
use OpenLoyalty\Component\EarningRule\Domain\ReferralEarningRule;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerFirstTransactionSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionSystemEvents;
use OpenLoyalty\Component\Account\Infrastructure\EarningRuleApplier;
use OpenLoyalty\Component\Account\Infrastructure\EarningRuleLimitValidator;
use OpenLoyalty\Component\Account\Domain\SystemEvent\QrcodeEventOccurredSystemEvent;

/**
 * Class ApplyEarningRuleToEventListener.
 */
class ApplyEarningRuleToEventListener extends BaseApplyEarningRuleListener
{
    /**
     * @var EarningRuleLimitValidator
     */
    protected $earningRuleLimitValidator;

    /**
     * ApplyEarningRuleToEventListener constructor.
     *
     * @param CommandBus             $commandBus
     * @param Repository             $accountDetailsRepository
     * @param UuidGeneratorInterface $uuidGenerator
     * @param EarningRuleApplier     $earningRuleApplier
     * @param $pointsTransfersManager        $pointsTransfersManager
     * @param EarningRuleLimitValidator|null $earningRuleLimitValidator
     */
    public function __construct(
        CommandBus $commandBus,
        Repository $accountDetailsRepository,
        UuidGeneratorInterface $uuidGenerator,
        EarningRuleApplier $earningRuleApplier,
        PointsTransfersManager $pointsTransfersManager,
        EarningRuleLimitValidator $earningRuleLimitValidator = null
    ) {
        parent::__construct($commandBus, $accountDetailsRepository, $uuidGenerator, $earningRuleApplier, $pointsTransfersManager);
        $this->earningRuleLimitValidator = $earningRuleLimitValidator;
    }

    /**
     * @param GeoEventOccurredSystemEvent $event
     */
    public function onCustomGeoEvent(GeoEventOccurredSystemEvent $event)
    {
        $result = [];
        $evaluationResultList = $this->earningRuleApplier->evaluateGeoEvent(
            $event->getLatitude(),
            $event->getLongitude(),
            (string) $event->getCustomerId(),
            $event->getEarningRuleId()
        );

        $account = $this->getAccountDetails((string) $event->getCustomerId());
        if (!$account) {
            return;
        }

        foreach ($evaluationResultList as $evaluationResult) {
            $validUsageLimit = $this->validateUsageLimit($evaluationResult->getEarningRuleId(), $event->getCustomerId());

            if ($validUsageLimit) {
                $result[] = $evaluationResult;
                $this->commandBus->dispatch(
                    new AddPoints(
                        $account->getAccountId(),
                        $this->pointsTransfersManager->createAddPointsTransferInstance(
                            new PointsTransferId($this->uuidGenerator->generate()),
                            $evaluationResult->getPoints(),
                            null,
                            false,
                            null,
                            $evaluationResult->getName()
                        )
                    )
                );
            }
        }

        if (count($result) === 0 && count($evaluationResultList) > 0) {
            throw new EarningRuleLimitExceededException();
        }

        $event->setEvaluationResults($result);
    }

    /**
     * @param string     $earningRuleId
     * @param CustomerId $customerId
     *
     * @return bool
     */
    protected function validateUsageLimit(string $earningRuleId, CustomerId $customerId): bool
    {
        if (!$this->earningRuleLimitValidator) {
            return true;
        }

        try {
            $this->earningRuleLimitValidator->validate($earningRuleId, $customerId);

            return true;
        } catch (EarningRuleLimitExceededException $e) {
            return false;
        }
    }

    /**
     * @param QrcodeEventOccurredSystemEvent $event
     *
     * @throws EarningRuleLimitExceededException
     */
    public function onCustomQrcodeEvent(QrcodeEventOccurredSystemEvent $event)
    {
        $result = [];
        $evaluationResultList = $this->earningRuleApplier->evaluateQrcodeEvent(
            $event->getCode(),
            (string) $event->getCustomerId(),
            $event->getEarningRuleId()
        );

        $account = $this->getAccountDetails((string) $event->getCustomerId());
        if (!$account) {
            return;
        }

        foreach ($evaluationResultList as $evaluationResult) {
            $validUsageLimit = $this->validateUsageLimit($evaluationResult->getEarningRuleId(), $event->getCustomerId());

            if ($validUsageLimit) {
                $result[] = $evaluationResult;
                $this->commandBus->dispatch(
                    new AddPoints(
                        $account->getAccountId(),
                        $this->pointsTransfersManager->createAddPointsTransferInstance(
                            new PointsTransferId($this->uuidGenerator->generate()),
                            $evaluationResult->getPoints(),
                            null,
                            false,
                            null,
                            $evaluationResult->getName()
                        )
                    )
                );
            }
        }

        if (count($result) === 0 && count($evaluationResultList) > 0) {
            throw new EarningRuleLimitExceededException();
        }

        $event->setEvaluationResults($result);
    }

    /**
     * @param CustomEventOccurredSystemEvent $event
     *
     * @throws EarningRuleLimitExceededException
     */
    public function onCustomEvent(CustomEventOccurredSystemEvent $event)
    {
        $result = $this->earningRuleApplier->evaluateCustomEvent($event->getEventName(), (string) $event->getCustomerId());
        if (null == $result || $result->getPoints() <= 0) {
            return;
        }
        $account = $this->getAccountDetails($event->getCustomerId()->__toString());
        if (!$account) {
            return;
        }
        if ($this->earningRuleLimitValidator) {
            $this->earningRuleLimitValidator->validate($result->getEarningRuleId(), $event->getCustomerId());
        }

        $this->commandBus->dispatch(
            new AddPoints($account->getAccountId(), $this->pointsTransfersManager->createAddPointsTransferInstance(
                new PointsTransferId($this->uuidGenerator->generate()),
                $result->getPoints(),
                null,
                false,
                null,
                $result->getName()
            ))
        );
        $event->setEvaluationResult($result);
    }

    /**
     * @param AccountCreatedSystemEvent $event
     */
    public function onCustomerRegistered(AccountCreatedSystemEvent $event)
    {
        $result = $this->earningRuleApplier->evaluateEventWithContext(
            AccountSystemEvents::ACCOUNT_CREATED,
            (string) $event->getCustomerId()
        );

        if (array_key_exists('points', $result) && $result['points'] > 0) {
            $this->commandBus->dispatch(
                new AddPoints(
                    $event->getAccountId(),
                    $this->pointsTransfersManager->createAddPointsTransferInstance(
                        new PointsTransferId($this->uuidGenerator->generate()),
                        $result['points'],
                        null,
                        false,
                        null,
                        $result['comment']
                    )
                )
            );
        }
    }

    /**
     * @param CustomerAttachedToInvitationSystemEvent $event
     */
    public function onCustomerAttachedToInvitation(CustomerAttachedToInvitationSystemEvent $event)
    {
        $this->evaluateReferral(ReferralEarningRule::EVENT_REGISTER, (string) $event->getCustomerId());
    }

    /**
     * @param CustomerFirstTransactionSystemEvent $event
     */
    public function onFirstTransaction(CustomerFirstTransactionSystemEvent $event)
    {
        $result = $this->earningRuleApplier->evaluateEventWithContext(
            TransactionSystemEvents::CUSTOMER_FIRST_TRANSACTION,
            (string) $event->getCustomerId()
        );
        $account = $this->getAccountDetails($event->getCustomerId()->__toString());

        if (!$account) {
            return;
        }

        if (array_key_exists('points', $result) && $result['points'] > 0) {
            $this->commandBus->dispatch(
                new AddPoints(
                    $account->getAccountId(),
                    $this->pointsTransfersManager->createAddPointsTransferInstance(
                        new PointsTransferId($this->uuidGenerator->generate()),
                        $result['points'],
                        null,
                        false,
                        null,
                        $result['comment']
                    )
                )
            );
        }

        $this->evaluateReferral(ReferralEarningRule::EVENT_FIRST_PURCHASE, (string) $event->getCustomerId());
    }

    /**
     * @param CustomerLoggedInSystemEvent $event
     */
    public function onCustomerLogin(CustomerLoggedInSystemEvent $event)
    {
        $result = $this->earningRuleApplier->evaluateEventWithContext(
            CustomerSystemEvents::CUSTOMER_LOGGED_IN,
            (string) $event->getCustomerId()
        );

        if (!array_key_exists('points', $result) || $result['points'] <= 0) {
            return;
        }
        $account = $this->getAccountDetails($event->getCustomerId()->__toString());

        if (!$account) {
            return;
        }

        $this->commandBus->dispatch(
            new AddPoints(
                $account->getAccountId(),
                $this->pointsTransfersManager->createAddPointsTransferInstance(
                    new PointsTransferId($this->uuidGenerator->generate()),
                    $result['points'],
                    null,
                    false,
                    null,
                    $result['comment']
                )
            )
        );
    }

    /**
     * @param NewsletterSubscriptionSystemEvent $event
     */
    public function onNewsletterSubscription(NewsletterSubscriptionSystemEvent $event)
    {
        $result = $this->earningRuleApplier->evaluateEventWithContext(
            CustomerSystemEvents::NEWSLETTER_SUBSCRIPTION,
            (string) $event->getCustomerId()
        );

        if (!array_key_exists('points', $result) || $result['points'] <= 0) {
            return;
        }
        $account = $this->getAccountDetails($event->getCustomerId()->__toString());

        if (!$account) {
            return;
        }

        $this->commandBus->dispatch(
            new AddPoints(
                $account->getAccountId(),
                $this->pointsTransfersManager->createAddPointsTransferInstance(
                    new PointsTransferId($this->uuidGenerator->generate()),
                    $result['points'],
                    null,
                    false,
                    null,
                    $result['comment']
                )
            )
        );
    }
}
