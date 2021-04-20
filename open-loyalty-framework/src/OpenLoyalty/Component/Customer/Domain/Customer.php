<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain;

use OpenLoyalty\Component\Campaign\Domain\Event\CampaignBoughtDeliveryStatusWasChanged;
use OpenLoyalty\Component\Core\Domain\SnapableEventSourcedAggregateRoot;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Customer\Domain\Event\AssignedAccountToCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\AssignedTransactionToCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerAvatarWasRemoved;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerAvatarWasSet;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignCouponWasChanged;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignStatusWasChanged;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignUsageWasChanged;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasBoughtByCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasReturned;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerDetailsWereUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerLevelWasRecalculated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasActivated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasDeactivated;
use OpenLoyalty\Component\Customer\Domain\Event\PosWasAssignedToCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasMovedToLevel;
use OpenLoyalty\Component\Customer\Domain\Event\SellerWasAssignedToCustomer;
use OpenLoyalty\Component\Customer\Domain\Model\Address;
use OpenLoyalty\Component\Customer\Domain\Model\Avatar;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\Model\Gender;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerAddressWasUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerCompanyDetailsWereUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerLoyaltyCardNumberWasUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\Model\Company;
use OpenLoyalty\Component\Customer\Domain\Model\Status;
use OpenLoyalty\Component\Customer\Domain\Model\Transaction;

/**
 * Class Customer.
 */
class Customer extends SnapableEventSourcedAggregateRoot
{
    /**
     * @var CustomerId
     */
    protected $id;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var Gender
     */
    protected $gender;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var \DateTime
     */
    protected $birthDate;

    /**
     * @var null|Address
     */
    protected $address;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var string
     */
    protected $loyaltyCardNumber;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $firstPurchaseAt;

    /**
     * @var bool
     */
    protected $agreement1 = false;

    /**
     * @var bool
     */
    protected $agreement2 = false;

    /**
     * @var bool
     */
    protected $agreement3 = false;

    /**
     * @var Company
     */
    protected $company = null;

    /**
     * @var LevelId
     */
    protected $levelId = null;

    /**
     * @var LevelId|null
     */
    protected $manuallyAssignedLevelId;

    /**
     * @var Label[]
     */
    protected $labels = [];

    /**
     * @var PosId|null
     */
    protected $posId;

    /**
     * @var SellerId|null
     */
    protected $sellerId;

    /**
     * @var CampaignPurchase[]
     */
    protected $campaignPurchases = [];

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var null|\DateTime
     */
    protected $lastLevelRecalculation;

    /**
     * @var Transaction[]
     */
    protected $transactions = [];

    /**
     * @var null|AccountId
     */
    protected $accountId;

    /**
     * @var null|Avatar
     */
    protected $avatar;

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return $this->id;
    }

    /**
     * @param CustomerId $customerId
     * @param array      $customerData
     *
     * @return Customer
     */
    public static function registerCustomer(CustomerId $customerId, array $customerData): Customer
    {
        $customer = new self();
        $customer->register($customerId, $customerData);

        return $customer;
    }

    /**
     * @param array $addressData
     */
    public function updateAddress(array $addressData): void
    {
        $this->apply(
            new CustomerAddressWasUpdated($this->id, $addressData)
        );
    }

    /**
     * @param array $companyData
     */
    public function updateCompanyDetails(array $companyData): void
    {
        $this->apply(
            new CustomerCompanyDetailsWereUpdated($this->id, $companyData)
        );
    }

    /**
     * @param $cardNumber
     */
    public function updateLoyaltyCardNumber($cardNumber): void
    {
        $this->apply(
            new CustomerLoyaltyCardNumberWasUpdated($this->id, $cardNumber)
        );
    }

    /**
     * @param LevelId|null $levelId
     * @param bool         $manually
     * @param bool         $removeLevelManually
     */
    public function addToLevel(LevelId $levelId = null, $manually = false, $removeLevelManually = false): void
    {
        $this->apply(
            new CustomerWasMovedToLevel($this->getId(), $levelId, $this->getLevelId(), $manually, $removeLevelManually)
        );
    }

    /**
     * @param AccountId $accountId
     */
    public function assignAccount(AccountId $accountId): void
    {
        $this->apply(
            new AssignedAccountToCustomer(
                $this->getId(),
                $accountId
            )
        );
    }

    /**
     * @param AssignedAccountToCustomer $event
     */
    protected function applyAssignedAccountToCustomer(AssignedAccountToCustomer $event): void
    {
        $this->setAccountId($event->getAccountId());
    }

    /**
     * @param AccountId $accountId
     */
    private function setAccountId(AccountId $accountId): void
    {
        $this->accountId = $accountId;
    }

    /**
     * @return AccountId|null
     */
    public function getAccountId(): ?AccountId
    {
        return $this->accountId;
    }

    /**
     * @param CustomerWasMovedToLevel $event
     */
    protected function applyCustomerWasMovedToLevel(CustomerWasMovedToLevel $event): void
    {
        $levelId = $event->getLevelId();

        $this->setLevelId($levelId);
        $this->setManuallyAssignedLevelId(null);

        if ($levelId && $event->isManually()) {
            $this->setManuallyAssignedLevelId($levelId);
        }
    }

    /**
     * @param LevelId|null $levelId
     */
    private function setLevelId(?LevelId $levelId): void
    {
        $this->levelId = $levelId;
    }

    /**
     * @param LevelId|null $manuallyAssignedLevelId
     */
    private function setManuallyAssignedLevelId(?LevelId $manuallyAssignedLevelId): void
    {
        $this->manuallyAssignedLevelId = $manuallyAssignedLevelId;
    }

    /**
     * @return LevelId|null
     */
    public function getManuallyAssignedLevelId(): ?LevelId
    {
        return $this->manuallyAssignedLevelId;
    }

    /**
     * @return null|LevelId
     */
    public function getLevelId(): ?LevelId
    {
        return $this->levelId;
    }

    /**
     * @param CustomerId $userId
     * @param array      $customerData
     */
    private function register(CustomerId $userId, array $customerData): void
    {
        $this->apply(
            new CustomerWasRegistered($userId, $customerData)
        );
    }

    /**
     * @param array $customerData
     */
    public function updateCustomerDetails(array $customerData): void
    {
        $this->apply(
            new CustomerDetailsWereUpdated($this->getId(), $customerData)
        );
    }

    /**
     * @param PosId $posId
     */
    public function assignPosToCustomer(PosId $posId): void
    {
        $this->apply(
            new PosWasAssignedToCustomer($this->getId(), $posId)
        );
    }

    /**
     * @param PosWasAssignedToCustomer $event
     */
    protected function applyPosWasAssignedToCustomer(PosWasAssignedToCustomer $event): void
    {
        $this->setPosId($event->getPosId());
    }

    /**
     * @param PosId $posId
     */
    private function setPosId(PosId $posId): void
    {
        $this->posId = $posId;
    }

    /**
     * @return null|PosId
     */
    public function getPosId(): ?PosId
    {
        return $this->posId;
    }

    /**
     * @param SellerId $sellerId
     */
    public function assignSellerToCustomer(SellerId $sellerId): void
    {
        $this->apply(
            new SellerWasAssignedToCustomer($this->getId(), $sellerId)
        );
    }

    /**
     * @param SellerWasAssignedToCustomer $event
     */
    protected function applySellerWasAssignedToCustomer(SellerWasAssignedToCustomer $event): void
    {
        $this->setSellerId($event->getSellerId());
    }

    /**
     * @param SellerId $sellerId
     */
    private function setSellerId(SellerId $sellerId): void
    {
        $this->sellerId = $sellerId;
    }

    /**
     * @return null|SellerId
     */
    public function getSellerId(): ?SellerId
    {
        return $this->sellerId;
    }

    /**
     * @param CampaignId $campaignId
     * @param $campaignName
     * @param $costInPoints
     * @param Coupon          $coupon
     * @param string          $reward
     * @param string          $status
     * @param \DateTime|null  $activeSince
     * @param \DateTime|null  $activeTo
     * @param null|Identifier $transactionId
     */
    public function buyCampaign(
        CampaignId $campaignId,
        $campaignName,
        $costInPoints,
        Coupon $coupon,
        string $reward,
        string $status,
        ?\DateTime $activeSince,
        ?\DateTime $activeTo,
        ?Identifier $transactionId
    ): void {
        $this->apply(
            new CampaignWasBoughtByCustomer(
                $this->getId(),
                $campaignId,
                $campaignName,
                $costInPoints,
                $coupon,
                $reward,
                $status,
                $activeSince,
                $activeTo,
                $transactionId
            )
        );
    }

    /**
     * @param CampaignWasBoughtByCustomer $event
     */
    protected function applyCampaignWasBoughtByCustomer(CampaignWasBoughtByCustomer $event): void
    {
        $this->addCampaignPurchase(
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
    }

    /**
     * @return CampaignPurchase[]
     */
    public function getCampaignPurchases(): array
    {
        return $this->campaignPurchases;
    }

    /**
     * @param CampaignPurchase $campaignPurchase
     */
    private function addCampaignPurchase(CampaignPurchase $campaignPurchase): void
    {
        $this->campaignPurchases[] = $campaignPurchase;
    }

    /**
     * @param TransactionId $transactionId
     * @param float         $grossValue
     * @param float         $grossValueWithoutDeliveryCosts
     * @param string        $documentNumber
     * @param int           $amountExcludedForLevel
     * @param bool          $isReturn
     * @param null|string   $revisedDocument
     */
    public function assignTransaction(
        TransactionId $transactionId,
        float $grossValue,
        float $grossValueWithoutDeliveryCosts,
        string $documentNumber,
        int $amountExcludedForLevel,
        bool $isReturn,
        ?string $revisedDocument
    ): void {
        $this->apply(
            new AssignedTransactionToCustomer(
                $this->getId(),
                $transactionId,
                $grossValue,
                $grossValueWithoutDeliveryCosts,
                $documentNumber,
                $amountExcludedForLevel,
                $isReturn,
                $revisedDocument
            )
        );
    }

    /**
     * @param AssignedTransactionToCustomer $event
     */
    public function applyAssignedTransactionToCustomer(AssignedTransactionToCustomer $event): void
    {
        $this->addTransaction(
            new Transaction(
                $event->getTransactionId(),
                $event->getGrossValue(),
                $event->getGrossValueWithoutDeliveryCosts(),
                $event->getDocumentNumber(),
                $event->getAmountExcludedForLevel(),
                $event->isReturn(),
                $event->getRevisedDocument()
            )
        );
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * @param Transaction $transaction
     */
    private function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    /**
     * @return float
     */
    public function getAmountExcludedForLevel(): float
    {
        $amountExcludedForLevel = 0.0;

        foreach ($this->getTransactions() as $transaction) {
            $amountExcludedForLevel += $transaction->getAmountExcludedForLevel();
        }

        return $amountExcludedForLevel;
    }

    /**
     * @return float
     */
    public function getAverageTransactionAmount(): float
    {
        return $this->getTransactionsCount() == 0 ? 0 : $this->getTransactionsAmount() / $this->getTransactionsCount();
    }

    /**
     * @return float
     */
    public function getTransactionsAmountWithoutDeliveryCosts(): float
    {
        $transactionsAmountWithoutDeliveryCosts = 0.0;

        foreach ($this->getTransactions() as $transaction) {
            $returnWithoutDeliveryAmount = 0;

            if ($transaction->isReturn()) {
                $revisedTransaction = $this->getTransactionByDocumentNumber($transaction->getRevisedDocument());
                if ($revisedTransaction instanceof Transaction) {
                    $grossValueWithoutDelivery = $transaction->getGrossValueWithoutDeliveryCosts();
                    // make return amount always negative
                    $returnWithoutDeliveryAmount = $grossValueWithoutDelivery > 0 ? ($grossValueWithoutDelivery * -1) : $grossValueWithoutDelivery;
                }
            }

            if ($returnWithoutDeliveryAmount < 0) {
                // if return transaction type: add a negative amount
                $transactionsAmountWithoutDeliveryCosts += $returnWithoutDeliveryAmount;
            } else {
                $transactionsAmountWithoutDeliveryCosts += $transaction->getGrossValueWithoutDeliveryCosts();
            }
        }

        return $transactionsAmountWithoutDeliveryCosts;
    }

    /**
     * @return float
     */
    public function getTransactionsAmount(): float
    {
        $transactionsAmount = 0.0;

        foreach ($this->getTransactions() as $transaction) {
            $returnAmount = 0;

            if ($transaction->isReturn() && $transaction->getRevisedDocument()) {
                $revisedTransaction = $this->getTransactionByDocumentNumber($transaction->getRevisedDocument());
                if ($revisedTransaction instanceof Transaction) {
                    $grossValue = $transaction->getGrossValue();
                    // make return amount always negative
                    $returnAmount = $grossValue > 0 ? ($grossValue * -1) : $grossValue;
                }
            }

            if ($returnAmount < 0) {
                $result = $transactionsAmount + $returnAmount;
                if ($result < 0) { // prevent a negative transaction's amount
                    $transactionsAmount = 0.0;
                } else {
                    $transactionsAmount = $result;
                }
            } else {
                $transactionsAmount += $transaction->getGrossValue();
            }
        }

        return $transactionsAmount;
    }

    /**
     * @return int
     */
    public function getTransactionsCount(): int
    {
        $transactionsCount = 0;
        $transactionsAmount = [];

        foreach ($this->getTransactions() as $transaction) {
            if (!$transaction->isReturn()) {
                ++$transactionsCount;
                $transactionsAmount[$transaction->getDocumentNumber()] = $transaction->getGrossValue();
            }

            if ($transaction->isReturn()) {
                $grossValue = $transaction->getGrossValue();
                // make return amount always negative
                $returnAmount = $grossValue > 0 ? ($grossValue * -1) : $grossValue;

                if (!array_key_exists($transaction->getRevisedDocument(), $transactionsAmount)) {
                    continue;
                }

                $transactionsAmount[$transaction->getRevisedDocument()] += $returnAmount;

                if ($transactionsAmount[$transaction->getRevisedDocument()] <= 0) {
                    --$transactionsCount;
                }
            }
        }

        // when someone creates more returns than amount available on sell transaction,
        // ie. sell = $10, return1 = $5, return2 = $5 and return3 = $2 the counter will be -1
        // so we need to prevent that
        if ($transactionsCount < 0) {
            $transactionsCount = 0;
        }

        return $transactionsCount;
    }

    /**
     * @param string $documentNumber
     *
     * @return null|Transaction
     */
    private function getTransactionByDocumentNumber(string $documentNumber): ?Transaction
    {
        foreach ($this->getTransactions() as $transaction) {
            if (!$transaction->isReturn() && $documentNumber === $transaction->getDocumentNumber()) {
                return $transaction;
            }
        }

        return null;
    }

    /**
     * @param CampaignId         $campaignId
     * @param Coupon             $coupon
     * @param bool               $used
     * @param null|\DateTime     $usageDate
     * @param null|TransactionId $transactionId
     */
    public function changeCampaignUsage(
        CampaignId $campaignId,
        Coupon $coupon,
        bool $used,
        ?\DateTime $usageDate,
        ?TransactionId $transactionId = null
    ): void {
        $this->apply(
            new CampaignUsageWasChanged(
                $this->getId(),
                $campaignId,
                $coupon,
                $used,
                $usageDate,
                $transactionId
            )
        );
    }

    /**
     * @param CampaignUsageWasChanged $event
     */
    protected function applyCampaignUsageWasChanged(CampaignUsageWasChanged $event): void
    {
        $campaignId = (string) $event->getCampaignId();
        $couponId = $event->getCoupon()->getId();
        $coupon = $event->getCoupon()->getCode();
        $transactionId = $event->getTransactionId();

        foreach ($this->getCampaignPurchases() as $purchase) {
            if ((string) $purchase->getCampaignId() === $campaignId
                && $purchase->getCoupon()->getCode() === $coupon
                && $purchase->getCoupon()->getId() === $couponId
                && $event->isUsed() !== $purchase->isUsed()) {
                $purchase->setUsed($event->isUsed());
                $purchase->setUsedForTransactionId($transactionId);
                $purchase->setUsageDate($event->getUsageDate());

                return;
            }
        }
    }

    /**
     * @param string $purchaseId
     * @param Coupon $coupon
     */
    public function campaignWasReturned(string $purchaseId, Coupon $coupon): void
    {
        $this->apply(
            new CampaignWasReturned($this->getId(), $purchaseId, $coupon)
        );
    }

    /**
     * @param string $path
     * @param string $originalName
     * @param string $mime
     */
    public function setAvatar(string $path, string $originalName, string $mime): void
    {
        $this->apply(
            new CustomerAvatarWasSet($this->getId(), $path, $originalName, $mime)
        );
    }

    /**
     * @param CustomerAvatarWasSet $event
     */
    protected function applyCustomerAvatarWasSet(CustomerAvatarWasSet $event): void
    {
        $this->avatar = new Avatar($event->getPath(), $event->getOriginalName(), $event->getMime());
    }

    /**
     * Remove avatar.
     */
    public function removeAvatar(): void
    {
        $this->apply(
            new CustomerAvatarWasRemoved($this->getId())
        );
    }

    /**
     * @param CustomerAvatarWasRemoved $event
     */
    protected function applyCustomerAvatarWasRemoved(CustomerAvatarWasRemoved $event): void
    {
        $this->avatar = null;
    }

    /**
     * @param CampaignWasReturned $event
     */
    protected function applyCampaignWasReturned(CampaignWasReturned $event): void
    {
        $coupon = $event->getCoupon()->getCode();
        $purchaseId = $event->getPurchaseId();

        foreach ($this->getCampaignPurchases() as $purchase) {
            if ($purchase->getId($event->getCustomerId()) === $purchaseId) {
                $purchase->setReturnedAmount($purchase->getReturnedAmount() + (float) $coupon);

                return;
            }
        }
    }

    /**
     * @param CampaignId         $campaignId
     * @param Coupon             $coupon
     * @param null|TransactionId $transactionId
     */
    public function expireCampaignBought(CampaignId $campaignId, Coupon $coupon, ?TransactionId $transactionId): void
    {
        $this->apply(
            new CampaignStatusWasChanged($this->getId(), $campaignId, $coupon, CampaignPurchase::STATUS_EXPIRED, $transactionId)
        );
    }

    /**
     * @param CampaignId         $campaignId
     * @param Coupon             $coupon
     * @param null|TransactionId $transactionId
     */
    public function activateCampaignBought(CampaignId $campaignId, Coupon $coupon, ?TransactionId $transactionId): void
    {
        $this->apply(
            new CampaignStatusWasChanged($this->getId(), $campaignId, $coupon, CampaignPurchase::STATUS_ACTIVE, $transactionId)
        );
    }

    /**
     * @param CampaignStatusWasChanged $event
     */
    protected function applyCampaignStatusWasChanged(CampaignStatusWasChanged $event): void
    {
        $campaignId = (string) $event->getCampaignId();
        $couponId = $event->getCoupon()->getId();
        $coupon = $event->getCoupon()->getCode();
        $transactionId = $event->getTransactionId() ? (string) $event->getTransactionId() : null;

        foreach ($this->getCampaignPurchases() as $purchase) {
            if ((string) $purchase->getCampaignId() === $campaignId
                && ($purchase->getTransactionId() ? (string) $purchase->getTransactionId() : null) === $transactionId
                && $purchase->getCoupon()->getCode() === $coupon
                && $purchase->getCoupon()->getId() === $couponId) {
                $purchase->setStatus($event->getStatus());

                return;
            }
        }
    }

    /**
     * @param CampaignId    $campaignId
     * @param TransactionId $transactionId
     * @param \DateTime     $createdAt
     * @param Coupon        $newCoupon
     */
    public function changeCampaignCoupon(CampaignId $campaignId, TransactionId $transactionId, \DateTime $createdAt, Coupon $newCoupon): void
    {
        $this->apply(
            new CampaignCouponWasChanged($this->getId(), $campaignId, $transactionId, $createdAt, $newCoupon)
        );
    }

    /**
     * @param CampaignCouponWasChanged $event
     */
    protected function applyCampaignCouponWasChanged(CampaignCouponWasChanged $event): void
    {
        $campaignId = (string) $event->getCampaignId();
        $transactionId = $event->getTransactionId() ? (string) $event->getTransactionId() : null;

        foreach ($this->getCampaignPurchases() as $purchase) {
            if ((string) $purchase->getCampaignId() === $campaignId
                && $purchase->getPurchaseAt() == $event->getCreatedAt()
                && ($purchase->getTransactionId() ? (string) $purchase->getTransactionId() : null === $transactionId)) {
                $purchase->setCoupon($event->getNewCoupon());

                return;
            }
        }
    }

    /**
     * @param CampaignId         $campaignId
     * @param Coupon             $coupon
     * @param null|TransactionId $transactionId
     */
    public function cancelCampaignBought(CampaignId $campaignId, Coupon $coupon, ?TransactionId $transactionId): void
    {
        $this->apply(
            new CampaignStatusWasChanged($this->getId(), $campaignId, $coupon, CampaignPurchase::STATUS_CANCELLED, $transactionId)
        );
    }

    /**
     * Deactivate.
     */
    public function deactivate(): void
    {
        $this->apply(
            new CustomerWasDeactivated($this->getId())
        );
    }

    /**
     * @param CustomerWasDeactivated $event
     */
    protected function applyCustomerWasDeactivated(CustomerWasDeactivated $event): void
    {
        $this->setActive(false);
        $this->setStatus(Status::typeBlocked());
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    private function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * Activate.
     */
    public function activate(): void
    {
        $this->apply(
            new CustomerWasActivated($this->getId())
        );
    }

    /**
     * @param CustomerWasActivated $event
     */
    protected function applyCustomerWasActivated(CustomerWasActivated $event): void
    {
        $this->setActive(true);
        $this->setStatus(Status::typeActiveNoCard());
    }

    /**
     * @param \DateTime $date
     */
    public function recalculateLevel(\DateTime $date): void
    {
        $this->apply(
            new CustomerLevelWasRecalculated($this->getId(), $date)
        );
    }

    /**
     * @param CustomerLevelWasRecalculated $event
     */
    protected function applyCustomerLevelWasRecalculated(CustomerLevelWasRecalculated $event): void
    {
        $this->setLastLevelRecalculation($event->getDate());
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLevelRecalculation(): ?\DateTime
    {
        return $this->lastLevelRecalculation;
    }

    /**
     * @param \DateTime|null $lastLevelRecalculation
     */
    private function setLastLevelRecalculation(?\DateTime $lastLevelRecalculation = null): void
    {
        $this->lastLevelRecalculation = $lastLevelRecalculation;
    }

    /**
     * @param CustomerWasRegistered $event
     */
    protected function applyCustomerWasRegistered(CustomerWasRegistered $event): void
    {
        $data = $event->getCustomerData();
        $data = $this->resolveOptions($data);

        $this->id = $event->getCustomerId();

        $this->setFirstName($data['firstName']);
        $this->setLastName($data['lastName']);

        if (isset($data['phone'])) {
            $this->setPhone($data['phone']);
        }
        if (isset($data['email'])) {
            $this->setEmail($data['email']);
        }
        if (isset($data['gender'])) {
            $this->setGender(new Gender($data['gender']));
        }
        if (isset($data['birthDate'])) {
            $this->setBirthDate($data['birthDate']);
        }
        if (isset($data['agreement1'])) {
            $this->setAgreement1($data['agreement1']);
        }
        if (isset($data['agreement2'])) {
            $this->setAgreement2($data['agreement2']);
        }
        if (isset($data['agreement3'])) {
            $this->setAgreement3($data['agreement3']);
        }
        $labels = [];
        if (isset($data['labels'])) {
            foreach ($data['labels'] as $label) {
                $labels[] = new Label($label['key'], $label['value']);
            }
        }
        $this->setLabels($labels);
        $this->setStatus(Status::typeNew());
        $this->setCreatedAt($data['createdAt']);
    }

    /**
     * @return Label[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @param Label[] $labels
     */
    public function setLabels(array $labels = []): void
    {
        $this->labels = $labels;
    }

    /**
     * @param CustomerDetailsWereUpdated $event
     */
    protected function applyCustomerDetailsWereUpdated(CustomerDetailsWereUpdated $event): void
    {
        $data = $event->getCustomerData();

        if (!empty($data['firstName'])) {
            $this->setFirstName($data['firstName']);
        }
        if (!empty($data['lastName'])) {
            $this->setLastName($data['lastName']);
        }
        if (isset($data['phone'])) {
            $this->setPhone($data['phone']);
        }
        if (array_key_exists('email', $data)) {
            $this->setEmail($data['email']);
        }
        if (!empty($data['gender'])) {
            $this->setGender(new Gender($data['gender']));
        }
        if (array_key_exists('birthDate', $data)) {
            $this->setBirthDate($data['birthDate']);
        }
        if (isset($data['agreement1'])) {
            $this->setAgreement1($data['agreement1']);
        }
        if (isset($data['agreement2'])) {
            $this->setAgreement2($data['agreement2']);
        }
        if (isset($data['agreement3'])) {
            $this->setAgreement3($data['agreement3']);
        }
        if (!empty($data['status'])) {
            $this->setStatus(Status::fromData($data['status']));
        }
        if (isset($data['labels'])) {
            $labels = [];
            foreach ($data['labels'] as $label) {
                $labels[] = new Label($label['key'], $label['value']);
            }
            $this->setLabels($labels);
        }
    }

    /**
     * @param CustomerAddressWasUpdated $event
     */
    protected function applyCustomerAddressWasUpdated(CustomerAddressWasUpdated $event): void
    {
        $this->setAddress(Address::fromData($event->getAddressData()));
    }

    /**
     * @param CustomerCompanyDetailsWereUpdated $event
     */
    protected function applyCustomerCompanyDetailsWereUpdated(CustomerCompanyDetailsWereUpdated $event): void
    {
        $companyData = $event->getCompanyData();
        if (!$companyData || count($companyData) == 0) {
            $this->setCompany(null);
        } else {
            $this->setCompany(new Company($companyData['name'], $event->getCompanyData()['nip']));
        }
    }

    /**
     * @param CustomerLoyaltyCardNumberWasUpdated $event
     */
    protected function applyCustomerLoyaltyCardNumberWasUpdated(CustomerLoyaltyCardNumberWasUpdated $event): void
    {
        $this->setLoyaltyCardNumber($event->getCardNumber());
    }

    /**
     * @return null|CustomerId
     */
    public function getId(): ?CustomerId
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return Gender
     */
    public function getGender(): Gender
    {
        return $this->gender;
    }

    /**
     * @param Gender $gender
     */
    public function setGender(Gender $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return \DateTime|null
     */
    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    /**
     * @param \DateTime|null $birthDate
     */
    public function setBirthDate(?\DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return null|Address
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * @param null|Address $address
     */
    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getLoyaltyCardNumber(): ?string
    {
        return $this->loyaltyCardNumber;
    }

    /**
     * @param string $loyaltyCardNumber
     */
    public function setLoyaltyCardNumber(string $loyaltyCardNumber): void
    {
        $this->loyaltyCardNumber = $loyaltyCardNumber;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getFirstPurchaseAt(): \DateTime
    {
        return $this->firstPurchaseAt;
    }

    /**
     * @param \DateTime $firstPurchaseAt
     */
    public function setFirstPurchaseAt(\DateTime $firstPurchaseAt): void
    {
        $this->firstPurchaseAt = $firstPurchaseAt;
    }

    /**
     * @return bool
     */
    public function isCompany(): bool
    {
        return $this->company != null ? true : false;
    }

    /**
     * @param Company $company
     */
    public function setCompany(Company $company = null): void
    {
        $this->company = $company;
    }

    /**
     * @return bool
     */
    public function isAgreement1(): bool
    {
        return $this->agreement1;
    }

    /**
     * @param bool $agreement1
     */
    public function setAgreement1(bool $agreement1): void
    {
        $this->agreement1 = $agreement1;
    }

    /**
     * @return bool
     */
    public function isAgreement2(): bool
    {
        return $this->agreement2;
    }

    /**
     * @param bool $agreement2
     */
    public function setAgreement2(bool $agreement2): void
    {
        $this->agreement2 = $agreement2;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @param Status $status
     */
    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isAgreement3(): bool
    {
        return $this->agreement3;
    }

    /**
     * @param bool $agreement3
     */
    public function setAgreement3(bool $agreement3): void
    {
        $this->agreement3 = $agreement3;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public static function resolveOptions(array $data): array
    {
        $defaults = [
            'firstName' => null,
            'lastName' => null,
            'address' => null,
            'status' => null,
            'gender' => null,
            'birthDate' => null,
            'company' => null,
            'loyaltyCardNumber' => null,
            'agreement1' => false,
            'agreement2' => false,
            'agreement3' => false,
        ];

        return array_merge($defaults, $data);
    }

    /**
     * @return null|Avatar
     */
    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    /**
     * @param string $couponId
     * @param string $deliveryStatus
     */
    public function changeCampaignBoughtDeliveryStatus(string $couponId, string $deliveryStatus): void
    {
        $this->apply(new CampaignBoughtDeliveryStatusWasChanged($couponId, $this->getId(), $deliveryStatus));
    }

    /**
     * @param CampaignBoughtDeliveryStatusWasChanged $changedEvent
     */
    protected function applyCampaignBoughtDeliveryStatusWasChanged(CampaignBoughtDeliveryStatusWasChanged $changedEvent): void
    {
        foreach ($this->getCampaignPurchases() as $campaignPurchase) {
            if ($campaignPurchase->getCoupon()->getId() === $changedEvent->getCouponId()) {
                $campaignPurchase->setDeliveryStatus($changedEvent->getStatus());
            }
        }
    }
}
