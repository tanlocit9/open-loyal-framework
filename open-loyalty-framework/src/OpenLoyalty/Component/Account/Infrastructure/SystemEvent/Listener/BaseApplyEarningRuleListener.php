<?php

namespace OpenLoyalty\Component\Account\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\ReadModel\Repository;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\PointsBundle\Service\PointsTransfersManager;
use OpenLoyalty\Component\Account\Domain\Command\AddPoints;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Infrastructure\PointsTransferManagerInterface;
use OpenLoyalty\Component\Customer\Domain\Command\InvitedCustomerMadePurchase;
use OpenLoyalty\Component\EarningRule\Domain\ReferralEarningRule;
use OpenLoyalty\Component\Account\Infrastructure\EarningRuleApplier;
use OpenLoyalty\Component\Account\Infrastructure\Model\ReferralEvaluationResult;

/**
 * Class BaseApplyEarningRuleToEventListener.
 */
abstract class BaseApplyEarningRuleListener
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Repository
     */
    protected $accountDetailsRepository;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var EarningRuleApplier
     */
    protected $earningRuleApplier;

    /**
     * @var PointsTransfersManager
     */
    protected $pointsTransfersManager;

    /**
     * ApplyEarningRuleToTransactionListener constructor.
     *
     * @param CommandBus                     $commandBus
     * @param Repository                     $accountDetailsRepository
     * @param UuidGeneratorInterface         $uuidGenerator
     * @param EarningRuleApplier             $earningRuleApplier
     * @param PointsTransferManagerInterface $pointsTransfersManager
     */
    public function __construct(
        CommandBus $commandBus,
        Repository $accountDetailsRepository,
        UuidGeneratorInterface $uuidGenerator,
        EarningRuleApplier $earningRuleApplier,
        PointsTransferManagerInterface $pointsTransfersManager
    ) {
        $this->commandBus = $commandBus;
        $this->accountDetailsRepository = $accountDetailsRepository;
        $this->uuidGenerator = $uuidGenerator;
        $this->earningRuleApplier = $earningRuleApplier;
        $this->pointsTransfersManager = $pointsTransfersManager;
    }

    /**
     * @param string $customerId
     *
     * @return null|AccountDetails
     */
    protected function getAccountDetails($customerId)
    {
        $accounts = $this->accountDetailsRepository->findBy(['customerId' => $customerId]);
        if (count($accounts) == 0) {
            return;
        }
        /** @var AccountDetails $account */
        $account = reset($accounts);

        if (!$account instanceof AccountDetails) {
            return;
        }

        return $account;
    }

    /**
     * @param string $eventName
     * @param string $customerId
     */
    protected function evaluateReferral(string $eventName, string $customerId): void
    {
        $results = $this->earningRuleApplier->evaluateReferralEvent($eventName, $customerId);

        /** @var ReferralEvaluationResult $result */
        foreach ($results as $result) {
            $rewardedCustomers = [];
            if ($result->getRewardType() == ReferralEarningRule::TYPE_BOTH) {
                $rewardedCustomers[] = [
                    'id' => $result->getInvitation()->getRecipientId(),
                    'comment' => sprintf('%s customer referral', (string) $result->getInvitation()->getReferrerId()),
                ];
                $rewardedCustomers[] = [
                    'id' => $result->getInvitation()->getReferrerId(),
                    'comment' => sprintf('Referring customer %s', (string) $result->getInvitation()->getRecipientId()),
                ];
            } elseif ($result->getRewardType() == ReferralEarningRule::TYPE_REFERRER) {
                $rewardedCustomers[] = [
                    'id' => $result->getInvitation()->getReferrerId(),
                    'comment' => sprintf('Referring customer %s', (string) $result->getInvitation()->getRecipientId()),
                ];
            } elseif ($result->getRewardType() == ReferralEarningRule::TYPE_REFERRED) {
                $rewardedCustomers[] = [
                    'id' => $result->getInvitation()->getRecipientId(),
                    'comment' => sprintf('%s customer referral', (string) $result->getInvitation()->getReferrerId()),
                ];
            }

            foreach ($rewardedCustomers as $customer) {
                $account = $this->getAccountDetails((string) $customer['id']);
                if (!$account) {
                    continue;
                }
                $this->commandBus->dispatch(
                    new AddPoints(
                        $account->getAccountId(),
                        $this->pointsTransfersManager->createAddPointsTransferInstance(
                            new PointsTransferId($this->uuidGenerator->generate()),
                            $result->getPoints(),
                            null,
                            false,
                            null,
                            $customer['comment']
                        )
                    )
                );
                if ($eventName == ReferralEarningRule::EVENT_EVERY_PURCHASE || $eventName == ReferralEarningRule::EVENT_FIRST_PURCHASE) {
                    $this->commandBus->dispatch(
                        new InvitedCustomerMadePurchase($result->getInvitation()->getInvitationId())
                    );
                }
            }
        }
    }
}
