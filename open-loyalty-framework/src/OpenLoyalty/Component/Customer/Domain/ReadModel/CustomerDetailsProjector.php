<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use OpenLoyalty\Component\Campaign\Domain\Event\CampaignBoughtDeliveryStatusWasChanged;
use OpenLoyalty\Component\Core\Infrastructure\Projector\Projector;
use Broadway\ReadModel\Repository;
use Broadway\Repository\Repository as AggregateRootRepository;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\Event\AssignedTransactionToCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignCouponWasChanged;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignStatusWasChanged;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignUsageWasChanged;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasBoughtByCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasReturned;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerAvatarWasRemoved;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerAvatarWasSet;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerDetailsWereUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerLevelWasRecalculated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasActivated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasDeactivated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasMovedToLevel;
use OpenLoyalty\Component\Customer\Domain\Event\PosWasAssignedToCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\SellerWasAssignedToCustomer;
use OpenLoyalty\Component\Customer\Domain\Model\Address;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Gender;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerAddressWasUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerCompanyDetailsWereUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerLoyaltyCardNumberWasUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\Model\Company;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\Status;
use OpenLoyalty\Component\Customer\Domain\TransactionId;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Level\Domain\ReadModel\LevelDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\Transaction;

/**
 * Class CustomerDetailsProjector.
 */
class CustomerDetailsProjector extends Projector
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var AggregateRootRepository
     */
    private $customerAggregateRootRepository;

    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var AggregateRootRepository
     */
    private $transactionRepository;

    /**
     * CustomerDetailsProjector constructor.
     *
     * @param Repository                   $repository
     * @param AggregateRootRepository      $customerAggregateRootRepository
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param LevelRepository              $levelRepository
     * @param AggregateRootRepository      $transactionRepository
     */
    public function __construct(
        Repository $repository,
        AggregateRootRepository $customerAggregateRootRepository,
        TransactionDetailsRepository $transactionDetailsRepository,
        LevelRepository $levelRepository,
        AggregateRootRepository $transactionRepository
    ) {
        $this->repository = $repository;
        $this->customerAggregateRootRepository = $customerAggregateRootRepository;
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->levelRepository = $levelRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param CustomerWasMovedToLevel $event
     */
    public function applyCustomerWasMovedToLevel(CustomerWasMovedToLevel $event): void
    {
        $customerId = $event->getCustomerId();
        $levelId = $event->getLevelId();

        /** @var CustomerDetails $customer */
        $customer = $this->getReadModel($customerId);

        $customer->setLevel(null);
        $customer->setLevelId($levelId);
        $customer->setManuallyAssignedLevelId(null);

        if ($levelId && $event->isManually()) {
            $customer->setManuallyAssignedLevelId($levelId);
        }

        if (null !== $levelId) {
            /** @var Level $level */
            $level = $this->levelRepository->byId(new LevelId((string) $levelId));
            if ($level) {
                $levelDetails = new LevelDetails($level->getLevelId());
                $levelDetails->setName($level->getName());
                $levelDetails->setTranslations($level->getTranslations());
                $customer->setLevel($levelDetails);
            }
        }

        $this->repository->save($customer);
    }

    /**
     * @param CustomerWasRegistered $event
     */
    protected function applyCustomerWasRegistered(CustomerWasRegistered $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());

        $data = $event->getCustomerData();
        $data = $readModel->resolveOptions($data);

        $readModel->setFirstName($data['firstName']);
        $readModel->setLastName($data['lastName']);

        if (isset($data['phone'])) {
            $readModel->setPhone($data['phone']);
        }
        if (isset($data['email'])) {
            $readModel->setEmail($data['email']);
        }
        if (isset($data['gender'])) {
            $readModel->setGender(new Gender($data['gender']));
        }
        if (isset($data['birthDate'])) {
            $readModel->setBirthDate($data['birthDate']);
        }
        if (isset($data['agreement1'])) {
            $readModel->setAgreement1($data['agreement1']);
        }
        if (isset($data['agreement2'])) {
            $readModel->setAgreement2($data['agreement2']);
        }
        if (isset($data['agreement3'])) {
            $readModel->setAgreement3($data['agreement3']);
        }
        $labels = [];
        if (isset($data['labels'])) {
            foreach ($data['labels'] as $label) {
                $labels[] = new Label($label['key'], $label['value']);
            }
        }
        $readModel->setLabels($labels);
        $readModel->setStatus(Status::typeNew());
        $readModel->setUpdatedAt($event->getUpdateAt());
        $readModel->setCreatedAt($data['createdAt']);

        $this->repository->save($readModel);
    }

    /**
     * @param CustomerDetailsWereUpdated $event
     */
    protected function applyCustomerDetailsWereUpdated(CustomerDetailsWereUpdated $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $data = $event->getCustomerData();

        if (!empty($data['firstName'])) {
            $readModel->setFirstName($data['firstName']);
        }
        if (!empty($data['lastName'])) {
            $readModel->setLastName($data['lastName']);
        }
        if (isset($data['phone'])) {
            $readModel->setPhone($data['phone']);
        }
        if (array_key_exists('email', $data)) {
            $readModel->setEmail($data['email']);
        }
        if (!empty($data['gender'])) {
            $readModel->setGender(new Gender($data['gender']));
        }
        if (array_key_exists('birthDate', $data)) {
            $readModel->setBirthDate($data['birthDate']);
        }
        if (isset($data['agreement1'])) {
            $readModel->setAgreement1($data['agreement1']);
        }
        if (isset($data['agreement2'])) {
            $readModel->setAgreement2($data['agreement2']);
        }
        if (isset($data['agreement3'])) {
            $readModel->setAgreement3($data['agreement3']);
        }
        if (isset($data['status'])) {
            $readModel->setStatus(Status::fromData($data['status']));
        }
        if (isset($data['labels'])) {
            $labels = [];
            foreach ($data['labels'] as $label) {
                $labels[] = new Label($label['key'], $label['value']);
            }
            $readModel->setLabels($labels);
        }
        $readModel->setUpdatedAt($event->getUpdateAt());

        $this->repository->save($readModel);
    }

    /**
     * @param CustomerAddressWasUpdated $event
     */
    protected function applyCustomerAddressWasUpdated(CustomerAddressWasUpdated $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setAddress(Address::fromData($event->getAddressData()));
        $readModel->setUpdatedAt($event->getUpdateAt());

        $this->repository->save($readModel);
    }

    /**
     * @param CustomerCompanyDetailsWereUpdated $event
     */
    protected function applyCustomerCompanyDetailsWereUpdated(CustomerCompanyDetailsWereUpdated $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $companyData = $event->getCompanyData();
        if (!$companyData || count($companyData) == 0) {
            $readModel->setCompany(null);
        } else {
            $readModel->setCompany(new Company($companyData['name'], $event->getCompanyData()['nip']));
        }
        $readModel->setUpdatedAt($event->getUpdateAt());

        $this->repository->save($readModel);
    }

    /**
     * @param CustomerLoyaltyCardNumberWasUpdated $event
     */
    protected function applyCustomerLoyaltyCardNumberWasUpdated(CustomerLoyaltyCardNumberWasUpdated $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setLoyaltyCardNumber($event->getCardNumber());
        $readModel->setUpdatedAt($event->getUpdateAt());

        $this->repository->save($readModel);
    }

    /**
     * @param PosWasAssignedToCustomer $event
     */
    protected function applyPosWasAssignedToCustomer(PosWasAssignedToCustomer $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setPosId($event->getPosId());
        $readModel->setUpdatedAt($event->getUpdateAt());

        $this->repository->save($readModel);
    }

    /**
     * @param SellerWasAssignedToCustomer $event
     */
    protected function applySellerWasAssignedToCustomer(SellerWasAssignedToCustomer $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setSellerId($event->getSellerId());
        $readModel->setUpdatedAt($event->getUpdateAt());

        $this->repository->save($readModel);
    }

    /**
     * @param CampaignWasBoughtByCustomer $event
     */
    protected function applyCampaignWasBoughtByCustomer(CampaignWasBoughtByCustomer $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->addCampaignPurchase(
            new CampaignPurchase(
                $event->getCreatedAt(),
                $event->getCostInPoints(),
                $event->getCampaignId(),
                $event->getCoupon(),
                $event->getReward(),
                $event->getStatus(),
                $event->getActiveSince(),
                $event->getActiveTo(),
                $event->getTransactionId()
            )
        );

        $this->repository->save($readModel);
    }

    /**
     * @param CampaignUsageWasChanged $event
     */
    protected function applyCampaignUsageWasChanged(CampaignUsageWasChanged $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());

        /** @var Customer $customer */
        $customer = $this->customerAggregateRootRepository->load($event->getCustomerId());

        $readModel->setCampaignPurchases($customer->getCampaignPurchases());
        $this->repository->save($readModel);
    }

    /**
     * @param CampaignWasReturned $event
     */
    protected function applyCampaignWasReturned(CampaignWasReturned $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());

        /** @var Customer $customer */
        $customer = $this->customerAggregateRootRepository->load($event->getCustomerId());

        $readModel->setCampaignPurchases($customer->getCampaignPurchases());
        $this->repository->save($readModel);
    }

    /**
     * @param CampaignStatusWasChanged $event
     */
    protected function applyCampaignStatusWasChanged(CampaignStatusWasChanged $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());

        /** @var Customer $customer */
        $customer = $this->customerAggregateRootRepository->load($event->getCustomerId());

        $readModel->setCampaignPurchases($customer->getCampaignPurchases());
        $this->repository->save($readModel);
    }

    /**
     * @param CustomerWasDeactivated $event
     */
    protected function applyCustomerWasDeactivated(CustomerWasDeactivated $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setActive(false);
        $readModel->setStatus(Status::typeBlocked());
        $this->repository->save($readModel);
    }

    /**
     * @param CustomerWasActivated $event
     */
    protected function applyCustomerWasActivated(CustomerWasActivated $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setActive(true);
        $readModel->setStatus(Status::typeActiveNoCard());
        $this->repository->save($readModel);
    }

    /**
     * @param CustomerLevelWasRecalculated $event
     */
    protected function applyCustomerLevelWasRecalculated(CustomerLevelWasRecalculated $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setLastLevelRecalculation($event->getDate());
        $this->repository->save($readModel);
    }

    /**
     * @param CustomerAvatarWasSet $event
     */
    protected function applyCustomerAvatarWasSet(CustomerAvatarWasSet $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setAvatarMime($event->getMime());
        $readModel->setAvatarOriginalName($event->getOriginalName());
        $readModel->setAvatarPath($event->getPath());
        $this->repository->save($readModel);
    }

    /**
     * @param CustomerAvatarWasRemoved $event
     */
    protected function applyCustomerAvatarWasRemoved(CustomerAvatarWasRemoved $event): void
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());
        $readModel->setAvatarMime(null);
        $readModel->setAvatarOriginalName(null);
        $readModel->setAvatarPath(null);
        $this->repository->save($readModel);
    }

    /**
     * @param AssignedTransactionToCustomer $event
     */
    public function applyAssignedTransactionToCustomer(AssignedTransactionToCustomer $event): void
    {
        $readModel = $this->getReadModel(new CustomerId((string) $event->getCustomerId()));

        /** @var Customer $customer */
        $customer = $this->customerAggregateRootRepository->load((string) $event->getCustomerId());
        $readModel->setTransactionsAmount($customer->getTransactionsAmount());
        $readModel->setTransactionsAmountWithoutDeliveryCosts($customer->getTransactionsAmountWithoutDeliveryCosts());
        $readModel->setTransactionsCount($customer->getTransactionsCount());
        $readModel->addTransactionId(new TransactionId((string) $event->getTransactionId()));
        $readModel->setAverageTransactionAmount($customer->getAverageTransactionAmount());
        $readModel->setAmountExcludedForLevel($customer->getAmountExcludedForLevel());

        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->load((string) $event->getTransactionId());
        if ($transaction->getPurchaseDate() > $readModel->getLastTransactionDate()) {
            $readModel->setLastTransactionDate($transaction->getPurchaseDate());
        }

        $this->repository->save($readModel);
    }

    /**
     * @param CampaignCouponWasChanged $event
     */
    protected function applyCampaignCouponWasChanged(CampaignCouponWasChanged $event)
    {
        /** @var CustomerDetails $readModel */
        $readModel = $this->getReadModel($event->getCustomerId());

        /** @var Customer $customer */
        $customer = $this->customerAggregateRootRepository->load($event->getCustomerId());

        $readModel->setCampaignPurchases($customer->getCampaignPurchases());
        $this->repository->save($readModel);
    }

    /**
     * @param CampaignBoughtDeliveryStatusWasChanged $changedEvent
     */
    protected function applyCampaignBoughtDeliveryStatusWasChanged(
        CampaignBoughtDeliveryStatusWasChanged $changedEvent
    ): void {
        $customerId = new CustomerId($changedEvent->getCustomerId());
        $customerDetails = $this->getReadModel($customerId);
        /** @var Customer $customer */
        $customer = $this->customerAggregateRootRepository->load($changedEvent->getCustomerId());
        $customerDetails->setCampaignPurchases($customer->getCampaignPurchases());

        $this->repository->save($customerDetails);
    }

    /**
     * @param CustomerId $userId
     *
     * @return null|CustomerDetails
     */
    private function getReadModel(CustomerId $userId): CustomerDetails
    {
        $readModel = $this->repository->find((string) $userId);

        if (null === $readModel) {
            $readModel = new CustomerDetails($userId);
        }

        return $readModel;
    }
}
