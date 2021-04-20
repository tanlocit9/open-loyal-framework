<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Bundle\AuditBundle\Service\AuditManagerInterface;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use OpenLoyalty\Component\Customer\Domain\Exception\EmailAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\LoyaltyCardNumberAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\PhoneAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CampaignUsageWasChangedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerActivatedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerAgreementsUpdatedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerDeactivatedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerLevelChangedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerRecalculateLevelRequestedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerRegisteredSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerRemovedManuallyLevelSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerUpdatedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\NewsletterSubscriptionSystemEvent;
use OpenLoyalty\Component\Customer\Domain\TransactionId;
use OpenLoyalty\Component\Campaign\Domain\TransactionId as CampaignTransactionId;
use OpenLoyalty\Component\Customer\Domain\Validator\CustomerUniqueValidator;
use OpenLoyalty\Component\Customer\Infrastructure\Exception\LevelDowngradeModeNotSupportedException;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;

/**
 * Class CustomerCommandHandler.
 */
class CustomerCommandHandler extends SimpleCommandHandler
{
    /**
     * @var CustomerRepository
     */
    private $repository;

    /**
     * @var CustomerUniqueValidator
     */
    private $customerUniqueValidator;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var AuditManagerInterface
     */
    private $auditManager;

    /**
     * @var LevelDowngradeModeProvider
     */
    private $levelDowngradeModeProvider;

    /**
     * CustomerCommandHandler constructor.
     *
     * @param CustomerRepository         $repository
     * @param CustomerUniqueValidator    $customerUniqueValidator
     * @param EventDispatcher            $eventDispatcher
     * @param AuditManagerInterface      $auditManager
     * @param LevelDowngradeModeProvider $levelDowngradeModeProvider
     */
    public function __construct(
        CustomerRepository $repository,
        CustomerUniqueValidator $customerUniqueValidator,
        EventDispatcher $eventDispatcher,
        AuditManagerInterface $auditManager,
        LevelDowngradeModeProvider $levelDowngradeModeProvider
    ) {
        $this->repository = $repository;
        $this->customerUniqueValidator = $customerUniqueValidator;
        $this->eventDispatcher = $eventDispatcher;
        $this->auditManager = $auditManager;
        $this->levelDowngradeModeProvider = $levelDowngradeModeProvider;
    }

    /**
     * @param RegisterCustomer $command
     *
     * @throws EmailAlreadyExistsException
     * @throws PhoneAlreadyExistsException
     */
    public function handleRegisterCustomer(RegisterCustomer $command)
    {
        $customerData = $command->getCustomerData();
        if (isset($customerData['email']) && $customerData['email']) {
            $this->customerUniqueValidator->validateEmailUnique($customerData['email']);
        }
        if (isset($customerData['phone']) && $customerData['phone']) {
            $this->customerUniqueValidator->validatePhoneUnique($customerData['phone']);
        }

        /** @var Customer $customer */
        $customer = Customer::registerCustomer($command->getCustomerId(), $customerData);
        $this->repository->save($customer);

        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_REGISTERED,
            [new CustomerRegisteredSystemEvent($command->getCustomerId(), $customerData)]
        );
    }

    /**
     * @param UpdateCustomerAddress $command
     */
    public function handleUpdateCustomerAddress(UpdateCustomerAddress $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load($customerId);
        $customer->updateAddress($command->getAddressData());
        $this->repository->save($customer);
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_UPDATED,
            [new CustomerUpdatedSystemEvent($customerId)]
        );
    }

    /**
     * @param UpdateCustomerCompanyDetails $command
     */
    public function handleUpdateCustomerCompanyDetails(UpdateCustomerCompanyDetails $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load($customerId);
        $customer->updateCompanyDetails($command->getCompanyData());
        $this->repository->save($customer);
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_UPDATED,
            [new CustomerUpdatedSystemEvent($customerId)]
        );
    }

    /**
     * @param UpdateCustomerLoyaltyCardNumber $command
     *
     * @throws LoyaltyCardNumberAlreadyExistsException
     */
    public function handleUpdateCustomerLoyaltyCardNumber(UpdateCustomerLoyaltyCardNumber $command)
    {
        $customerId = $command->getCustomerId();
        $this->customerUniqueValidator->validateLoyaltyCardNumberUnique($command->getCardNumber(), $customerId);
        /** @var Customer $customer */
        $customer = $this->repository->load($customerId);
        $customer->updateLoyaltyCardNumber($command->getCardNumber());
        $this->repository->save($customer);
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_UPDATED,
            [new CustomerUpdatedSystemEvent($customerId)]
        );
    }

    /**
     * @param UpdateCustomerDetails $command
     *
     * @throws EmailAlreadyExistsException
     * @throws PhoneAlreadyExistsException
     */
    public function handleUpdateCustomerDetails(UpdateCustomerDetails $command)
    {
        $customerId = $command->getCustomerId();
        $customerData = $command->getCustomerData();
        if (isset($customerData['email'])) {
            $this->customerUniqueValidator->validateEmailUnique($customerData['email'], $customerId);
        }
        if (isset($customerData['phone']) && $customerData['phone']) {
            $this->customerUniqueValidator->validatePhoneUnique($customerData['phone'], $customerId);
        }
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $oldAgreements = [
            'agreement1' => $customer->isAgreement1(),
            'agreement2' => $customer->isAgreement2(),
            'agreement3' => $customer->isAgreement3(),
        ];

        $customer->updateCustomerDetails($customerData);
        $this->repository->save($customer);

        $newAgreements = [
            'agreement1' => [
                'new' => $customer->isAgreement1(),
                'old' => $oldAgreements['agreement1'],
            ],
            'agreement2' => [
                'new' => $customer->isAgreement2(),
                'old' => $oldAgreements['agreement2'],
            ],
            'agreement3' => [
                'new' => $customer->isAgreement3(),
                'old' => $oldAgreements['agreement3'],
            ],
        ];

        foreach ($newAgreements as $key => $agr) {
            if ($agr['new'] === $agr['old']) {
                unset($newAgreements[$key]);
            }
        }

        if (count($newAgreements) > 0) {
            $this->auditManager->auditCustomerEvent(
                AuditManagerInterface::AGREEMENTS_UPDATED_CUSTOMER_EVENT_TYPE,
                $customerId,
                $newAgreements
            );

            $this->eventDispatcher->dispatch(
                CustomerSystemEvents::CUSTOMER_AGREEMENTS_UPDATED,
                [new CustomerAgreementsUpdatedSystemEvent($customerId, $newAgreements)]
            );
        }

        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_UPDATED,
            [new CustomerUpdatedSystemEvent($customerId)]
        );
    }

    /**
     * @param MoveCustomerToLevel $command
     *
     * @throws LevelDowngradeModeNotSupportedException
     */
    public function handleMoveCustomerToLevel(MoveCustomerToLevel $command)
    {
        $customerId = $command->getCustomerId();

        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->addToLevel($command->getLevelId(), $command->isManually(), $command->isRemoveLevelManually());
        if ($this->levelDowngradeModeProvider->getMode() === LevelDowngradeModeProvider::MODE_X_DAYS
            && $this->levelDowngradeModeProvider->getBase() === LevelDowngradeModeProvider::BASE_EARNED_POINTS_SINCE_LAST_LEVEL_CHANGE) {
            $customer->recalculateLevel($command->getDateTime());
        }

        $this->repository->save($customer);

        $this->eventDispatcher->dispatch(CustomerSystemEvents::CUSTOMER_UPDATED, [
            new CustomerUpdatedSystemEvent($customerId),
        ]);

        $this->eventDispatcher->dispatch(CustomerSystemEvents::CUSTOMER_LEVEL_CHANGED, [
            new CustomerLevelChangedSystemEvent($customerId, $command->getLevelId(), $command->getLevelName()),
        ]);
    }

    /**
     * @param AssignPosToCustomer $command'
     */
    public function handleAssignPosToCustomer(AssignPosToCustomer $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->assignPosToCustomer($command->getPosId());
        $this->repository->save($customer);
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_UPDATED,
            [new CustomerUpdatedSystemEvent($customerId)]
        );
    }

    /**
     * @param AssignSellerToCustomer $command
     */
    public function handleAssignSellerToCustomer(AssignSellerToCustomer $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->assignSellerToCustomer($command->getSellerId());
        $this->repository->save($customer);
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_UPDATED,
            [new CustomerUpdatedSystemEvent($customerId)]
        );
    }

    /**
     * @param BuyCustomerCampaign $command
     */
    public function handleBuyCustomerCampaign(BuyCustomerCampaign $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->buyCampaign(
            $command->getCampaignId(),
            $command->getCampaignName(),
            $command->getCostInPoints(),
            $command->getCoupon(),
            $command->getReward(),
            $command->getStatus(),
            $command->getActiveSince(),
            $command->getActiveTo(),
            $command->getTransactionId()
        );
        $this->repository->save($customer);
    }

    /**
     * @param ReturnCustomerCampaign $command
     */
    public function handleReturnCustomerCampaign(ReturnCustomerCampaign $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->buyCampaign(
            $command->getCampaignId(),
            $command->getCampaignName(),
            $command->getCostInPoints(),
            $command->getCoupon(),
            $command->getReward(),
            $command->getStatus(),
            $command->getActiveSince(),
            $command->getActiveTo(),
            new CampaignTransactionId((string) $command->getTransactionId())
        );
        $customer->campaignWasReturned($command->getPurchaseId(), $command->getCoupon());

        $this->repository->save($customer);
    }

    /**
     * @param ChangeCampaignUsage $command
     */
    public function handleChangeCampaignUsage(ChangeCampaignUsage $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->changeCampaignUsage(
            $command->getCampaignId(),
            $command->getCoupon(),
            $command->isUsed(),
            $command->getUsageDate(),
            $command->getTransactionId()
        );
        $this->repository->save($customer);

        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_CAMPAIGN_USAGE_WAS_CHANGED,
            [
                new CampaignUsageWasChangedSystemEvent(
                    $customerId,
                    $command->getCampaignId(),
                    $command->getCoupon(),
                    $command->getTransactionId(),
                    $command->isUsed()
                ),
            ]
        );
    }

    /**
     * @param ActivateBoughtCampaign $command
     */
    public function handleActivateBoughtCampaign(ActivateBoughtCampaign $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->activateCampaignBought($command->getCampaignId(), $command->getCoupon(), $command->getTransactionId());
        $this->repository->save($customer);
    }

    /**
     * @param ExpireBoughtCampaign $command
     */
    public function handleExpireBoughtCampaign(ExpireBoughtCampaign $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->expireCampaignBought($command->getCampaignId(), $command->getCoupon(), $command->getTransactionId());
        $this->repository->save($customer);
    }

    /**
     * @param DeactivateCustomer $command
     */
    public function handleDeactivateCustomer(DeactivateCustomer $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->deactivate();
        $this->repository->save($customer);
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_DEACTIVATED,
            [new CustomerDeactivatedSystemEvent($customerId)]
        );
    }

    /**
     * @param ActivateCustomer $command
     */
    public function handleActivateCustomer(ActivateCustomer $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->activate();
        $this->repository->save($customer);
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_ACTIVATED,
            [new CustomerActivatedSystemEvent($customerId)]
        );
    }

    /**
     * @param NewsletterSubscription $command
     */
    public function handleNewsletterSubscription(NewsletterSubscription $command)
    {
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::NEWSLETTER_SUBSCRIPTION,
            [new NewsletterSubscriptionSystemEvent($command->getCustomerId())]
        );
    }

    /**
     * @param RemoveManuallyAssignedLevel $command
     */
    public function handleRemoveManuallyAssignedLevel(RemoveManuallyAssignedLevel $command)
    {
        $customerId = $command->getCustomerId();

        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_MANUALLY_LEVEL_REMOVED,
            [new CustomerRemovedManuallyLevelSystemEvent($customerId)]
        );

        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_UPDATED,
            [new CustomerUpdatedSystemEvent($customerId)]
        );
    }

    /**
     * @param UpdateBoughtCampaignCouponCommand $command
     */
    public function handleUpdateBoughtCampaignCouponCommand(UpdateBoughtCampaignCouponCommand $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load($customerId);
        $customer->changeCampaignCoupon(
            new CampaignId($command->getCampaignId()),
            new TransactionId($command->getTransactionId()),
            $command->getCreatedAt(),
            new Coupon(
                $command->getCouponId(),
                $command->getNewCoupon()
            )
        );
        $this->repository->save($customer);
    }

    /**
     * @param CancelBoughtCampaign $command
     */
    public function handleCancelBoughtCampaign(CancelBoughtCampaign $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load($customerId);

        $customer->cancelCampaignBought(
            $command->getCampaignId(),
            $command->getCoupon(),
            $command->getTransactionId()
        );

        $this->repository->save($customer);
    }

    /**
     * @param RecalculateCustomerLevel $command
     */
    public function handleRecalculateCustomerLevel(RecalculateCustomerLevel $command)
    {
        $customerId = $command->getCustomerId();
        $date = $command->getDate();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $this->eventDispatcher->dispatch(
            CustomerSystemEvents::CUSTOMER_RECALCULATE_LEVEL_REQUESTED,
            [new CustomerRecalculateLevelRequestedSystemEvent($customerId)]
        );
        $customer->recalculateLevel($date);
        $this->repository->save($customer);
    }

    /**
     * @param AssignTransactionToCustomer $command
     */
    public function handleAssignTransactionToCustomer(AssignTransactionToCustomer $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->assignTransaction(
            $command->getTransactionId(),
            $command->getGrossValue(),
            $command->getGrossValueWithoutDeliveryCosts(),
            $command->getDocumentNumber(),
            $command->getAmountExcludedForLevel(),
            $command->isReturn(),
            $command->getRevisedDocument()
        );

        $this->repository->save($customer);
    }

    /**
     * @param AssignAccountToCustomer $command
     */
    public function handleAssignAccountToCustomer(AssignAccountToCustomer $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);
        $customer->assignAccount(
            $command->getAccountId()
        );
        $this->repository->save($customer);
    }

    /**
     * @param SetAvatar $command'
     */
    public function handleSetAvatar(SetAvatar $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);

        $customer->setAvatar($command->getPath(), $command->getOriginalName(), $command->getMime());
        $this->repository->save($customer);
    }

    /**
     * @param RemoveAvatar $command'
     */
    public function handleRemoveAvatar(RemoveAvatar $command)
    {
        $customerId = $command->getCustomerId();
        /** @var Customer $customer */
        $customer = $this->repository->load((string) $customerId);

        $customer->removeAvatar();
        $this->repository->save($customer);
    }
}
