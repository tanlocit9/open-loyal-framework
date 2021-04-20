<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Infrastructure\Event\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListener;
use Broadway\ReadModel\Repository;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Account\Domain\Command\SpendPoints;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasBoughtByCustomer;

/**
 * Class SpendPointsOnCampaignListener.
 */
class SpendPointsOnCampaignListener implements EventListener
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
     * SpendPointsOnCampaignListener constructor.
     *
     * @param CommandBus             $commandBus
     * @param Repository             $accountDetailsRepository
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(
        CommandBus $commandBus,
        Repository $accountDetailsRepository,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->commandBus = $commandBus;
        $this->accountDetailsRepository = $accountDetailsRepository;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function onCustomerBoughtCampaign(CampaignWasBoughtByCustomer $event)
    {
        $points = $event->getCostInPoints();
        if ($points == 0) {
            return;
        }

        $customerId = $event->getCustomerId();
        $accounts = $this->accountDetailsRepository->findBy(['customerId' => $customerId->__toString()]);
        if (count($accounts) == 0) {
            return;
        }
        /** @var AccountDetails $account */
        $account = reset($accounts);

        $this->commandBus->dispatch(
            new SpendPoints(
                $account->getAccountId(),
                new SpendPointsTransfer(
                    new PointsTransferId($this->uuidGenerator->generate()),
                    $points,
                    null,
                    false,
                    $event->getReward() == Campaign::REWARD_TYPE_CASHBACK ? ($event->getCampaignName().', cashback') : ($event->getCampaignName().', coupon: '.$event->getCoupon()->getCode())
                )
            )
        );
    }

    /**
     * @param DomainMessage $domainMessage
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();
        if ($event instanceof CampaignWasBoughtByCustomer) {
            $this->onCustomerBoughtCampaign($event);
        }
    }
}
