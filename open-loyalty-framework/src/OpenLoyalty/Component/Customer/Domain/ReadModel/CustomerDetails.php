<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\Model\Address;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\Model\Gender;
use OpenLoyalty\Component\Customer\Domain\Model\Company;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\LevelId;
use OpenLoyalty\Component\Customer\Domain\Model\Status;
use OpenLoyalty\Component\Customer\Domain\PosId;
use OpenLoyalty\Component\Customer\Domain\SellerId;
use OpenLoyalty\Component\Customer\Domain\TransactionId;
use OpenLoyalty\Component\Level\Domain\ReadModel\LevelDetails;

/**
 * Class CustomerDetails.
 */
class CustomerDetails implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var PosId
     */
    protected $posId = null;

    /**
     * @var SellerId
     */
    protected $sellerId = null;

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
     * @var \DateTime
     */
    protected $lastLevelRecalculation;

    /**
     * @var Address
     */
    protected $address;

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
     * @var LevelId
     */
    protected $levelId;

    /**
     * @var LevelId
     */
    protected $manuallyAssignedLevelId;

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
     * @var Status
     */
    protected $status;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var CampaignPurchase[]
     */
    protected $campaignPurchases = [];

    /**
     * @var int
     */
    protected $transactionsCount = 0;

    /**
     * @var float
     */
    protected $transactionsAmount = 0;

    /**
     * @var float
     */
    protected $transactionsAmountWithoutDeliveryCosts = 0;

    /**
     * @var float
     */
    protected $amountExcludedForLevel = 0;

    /**
     * @var float
     */
    protected $averageTransactionAmount = 0;

    /**
     * @var TransactionId[]
     */
    protected $transactionIds = [];

    /**
     * @var \DateTime
     */
    protected $lastTransactionDate;

    /**
     * @var Label[]
     */
    protected $labels = [];

    /**
     * @var LevelDetails|null
     */
    protected $level;

    /**
     * @var null|string
     */
    private $avatarPath;

    /**
     * @var null|string
     */
    private $avatarOriginalName;

    /**
     * @var null|string
     */
    private $avatarMime;

    /**
     * CustomerDetails constructor.
     *
     * @param CustomerId $id
     */
    public function __construct(CustomerId $id)
    {
        $this->customerId = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->customerId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return Label[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param Label[] $labels
     */
    public function setLabels(array $labels = [])
    {
        $this->labels = $labels;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $data = static::resolveOptions($data);
        $customer = new self(new CustomerId($data['id']));
        $customer->setFirstName($data['firstName']);
        $customer->setLastName($data['lastName']);

        if (isset($data['phone'])) {
            $customer->setPhone($data['phone']);
        }

        if (!empty($data['gender'])) {
            $customer->setGender(new Gender($data['gender']));
        }

        if (isset($data['email'])) {
            $customer->setEmail($data['email']);
        }

        if (!empty($data['birthDate'])) {
            if ($data['birthDate'] instanceof \DateTime) {
                $birthDate = $data['birthDate'];
            } else {
                $birthDate = new \DateTime();
                $birthDate->setTimestamp($data['birthDate']);
            }
            $customer->setBirthDate($birthDate);
        }

        if (!empty($data['lastLevelRecalculation'])) {
            if ($data['lastLevelRecalculation'] instanceof \DateTime) {
                $lastLevelRecalculation = $data['lastLevelRecalculation'];
            } else {
                $lastLevelRecalculation = new \DateTime();
                $lastLevelRecalculation->setTimestamp($data['lastLevelRecalculation']);
            }
            $customer->setLastLevelRecalculation($lastLevelRecalculation);
        }

        if (isset($data['createdAt'])) {
            if ($data['createdAt'] instanceof \DateTime) {
                $createdAt = $data['createdAt'];
            } else {
                $createdAt = new \DateTime();
                $createdAt->setTimestamp($data['createdAt']);
            }
        } else {
            $createdAt = new \DateTime();
        }

        $customer->setCreatedAt($createdAt);

        if (isset($data['address'])) {
            $customer->setAddress(Address::deserialize($data['address']));
        }

        if (isset($data['status'])) {
            $customer->setStatus(Status::deserialize($data['status']));
        }

        if (isset($data['company'])) {
            $customer->setCompany(Company::deserialize($data['company']));
        }
        if (isset($data['loyaltyCardNumber'])) {
            $customer->setLoyaltyCardNumber($data['loyaltyCardNumber']);
        }
        if (isset($data['levelId']) && $data['levelId']) {
            $customer->setLevelId(new LevelId($data['levelId']));
        }

        if (isset($data['manuallyAssignedLevelId']) && $data['manuallyAssignedLevelId']) {
            $customer->setManuallyAssignedLevelId(new LevelId($data['manuallyAssignedLevelId']));
        }

        if (isset($data['posId'])) {
            $customer->posId = new PosId($data['posId']);
        }

        if (isset($data['sellerId'])) {
            $customer->sellerId = new SellerId($data['sellerId']);
        }

        if (isset($data['agreement1'])) {
            $customer->agreement1 = $data['agreement1'];
        }

        if (isset($data['agreement2'])) {
            $customer->agreement2 = $data['agreement2'];
        }

        if (isset($data['agreement3'])) {
            $customer->agreement3 = $data['agreement3'];
        }

        if (isset($data['updatedAt'])) {
            $date = new \DateTime();
            $date->setTimestamp($data['updatedAt']);
            $customer->setUpdatedAt($date);
        }
        if (isset($data['campaignPurchases'])) {
            $campaigns = array_map(function ($model) {
                return CampaignPurchase::deserialize($model);
            }, $data['campaignPurchases']);
            $customer->setCampaignPurchases($campaigns);
        }

        if (isset($data['active'])) {
            $customer->active = $data['active'];
        }

        if (isset($data['transactionsCount'])) {
            $customer->setTransactionsCount($data['transactionsCount']);
        }
        if (isset($data['transactionsAmount'])) {
            $customer->setTransactionsAmount($data['transactionsAmount']);
        }
        if (isset($data['amountExcludedForLevel'])) {
            $customer->setAmountExcludedForLevel($data['amountExcludedForLevel']);
        }
        if (isset($data['transactionsAmountWithoutDeliveryCosts'])) {
            $customer->setTransactionsAmountWithoutDeliveryCosts($data['transactionsAmountWithoutDeliveryCosts']);
        }
        if (isset($data['averageTransactionAmount'])) {
            $customer->setAverageTransactionAmount($data['averageTransactionAmount']);
        }
        if (isset($data['transactionIds'])) {
            $customer->setTransactionIds(array_map(function ($id) {
                return new TransactionId($id);
            }, $data['transactionIds']));
        }

        if (isset($data['lastTransactionDate'])) {
            $tmp = new \DateTime();
            $tmp->setTimestamp($data['lastTransactionDate']);
            $customer->setLastTransactionDate($tmp);
        }

        if (isset($data['level'])) {
            $customer->level = LevelDetails::deserialize($data['level']);
        }

        $labels = [];
        if (isset($data['labels'])) {
            foreach ($data['labels'] as $label) {
                $labels[] = Label::deserialize($label);
            }
        }
        $customer->setLabels($labels);

        if (isset($data['avatarPath'])) {
            $customer->avatarPath = $data['avatarPath'];
        }
        if (isset($data['avatarMime'])) {
            $customer->avatarMime = $data['avatarMime'];
        }
        if (isset($data['avatarOriginalName'])) {
            $customer->avatarOriginalName = $data['avatarOriginalName'];
        }

        return $customer;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        $serializedCampaigns = array_map(function (CampaignPurchase $campaignPurchase) {
            return $campaignPurchase->serialize();
        }, $this->campaignPurchases);

        $labels = [];
        foreach ($this->labels as $label) {
            $labels[] = $label->serialize();
        }

        return [
            'id' => $this->getId(),
            'customerId' => (string) $this->customerId,
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'gender' => $this->getGender() ? $this->getGender()->getType() : null,
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'birthDate' => $this->getBirthDate() ? $this->getBirthDate()->getTimestamp() : null,
            'lastLevelRecalculation' => $this->getLastLevelRecalculation() ? $this->getLastLevelRecalculation()->getTimestamp() : null,
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->getTimestamp() : null,
            'address' => $this->getAddress() ? $this->getAddress()->serialize() : null,
            'company' => $this->getCompany() ? $this->getCompany()->serialize() : null,
            'loyaltyCardNumber' => $this->getLoyaltyCardNumber(),
            'levelId' => $this->getLevelId() ? (string) $this->getLevelId() : null,
            'manuallyAssignedLevelId' => $this->getManuallyAssignedLevelId() ? (string) $this->getManuallyAssignedLevelId() : null,
            'posId' => $this->getPosId() ? (string) $this->getPosId() : null,
            'sellerId' => $this->getSellerId() ? (string) $this->getSellerId() : null,
            'agreement1' => $this->agreement1,
            'agreement2' => $this->agreement2,
            'agreement3' => $this->agreement3,
            'updatedAt' => $this->updatedAt ? $this->updatedAt->getTimestamp() : null,
            'campaignPurchases' => $serializedCampaigns ?: [],
            'active' => $this->active,
            'status' => $this->getStatus() ? $this->getStatus()->serialize() : null,
            'transactionsCount' => $this->transactionsCount,
            'transactionsAmount' => $this->transactionsAmount,
            'transactionsAmountWithoutDeliveryCosts' => $this->transactionsAmountWithoutDeliveryCosts,
            'averageTransactionAmount' => $this->averageTransactionAmount,
            'amountExcludedForLevel' => $this->amountExcludedForLevel,
            'lastTransactionDate' => $this->lastTransactionDate ? $this->lastTransactionDate->getTimestamp() : null,
            'labels' => $labels,
            'level' => $this->getLevel() ? $this->getLevel()->serialize() : null,
            'transactionIds' => array_map(function (TransactionId $transactionId) {
                return (string) $transactionId;
            }, $this->transactionIds),
            'avatarPath' => $this->getAvatarPath(),
            'avatarMime' => $this->getAvatarMime(),
            'avatarOriginalName' => $this->getAvatarOriginalName(),
        ];
    }

    /**
     * @return PosId
     */
    public function getPosId()
    {
        return $this->posId;
    }

    /**
     * @param PosId $posId
     */
    public function setPosId($posId)
    {
        $this->posId = $posId;
    }

    /**
     * @return SellerId
     */
    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * @param SellerId $sellerId
     */
    public function setSellerId($sellerId)
    {
        $this->sellerId = $sellerId;
    }

    /**
     * @return LevelId
     */
    public function getLevelId(): ?LevelId
    {
        return $this->levelId;
    }

    /**
     * @param LevelId $levelId
     */
    public function setLevelId($levelId)
    {
        $this->levelId = $levelId;
    }

    /**
     * @return LevelId|null
     */
    public function getManuallyAssignedLevelId(): ?LevelId
    {
        return $this->manuallyAssignedLevelId;
    }

    /**
     * @param LevelId|null $manuallyAssignedLevelId
     */
    public function setManuallyAssignedLevelId(?LevelId $manuallyAssignedLevelId): void
    {
        $this->manuallyAssignedLevelId = $manuallyAssignedLevelId;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param Gender $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return \DateTime|null
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param \DateTime|null $birthDate
     */
    public function setBirthDate(?\DateTime $birthDate)
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getLoyaltyCardNumber()
    {
        return $this->loyaltyCardNumber;
    }

    /**
     * @param string $loyaltyCardNumber
     */
    public function setLoyaltyCardNumber($loyaltyCardNumber)
    {
        $this->loyaltyCardNumber = $loyaltyCardNumber;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getFirstPurchaseAt()
    {
        return $this->firstPurchaseAt;
    }

    /**
     * @param \DateTime $firstPurchaseAt
     */
    public function setFirstPurchaseAt($firstPurchaseAt)
    {
        $this->firstPurchaseAt = $firstPurchaseAt;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param Company $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return bool
     */
    public function isAgreement1()
    {
        return $this->agreement1;
    }

    /**
     * @param bool $agreement1
     */
    public function setAgreement1($agreement1)
    {
        $this->agreement1 = $agreement1;
    }

    /**
     * @return bool
     */
    public function isAgreement2()
    {
        return $this->agreement2;
    }

    /**
     * @param bool $agreement2
     */
    public function setAgreement2($agreement2)
    {
        $this->agreement2 = $agreement2;
    }

    /**
     * @return bool
     */
    public function isAgreement3()
    {
        return $this->agreement3;
    }

    /**
     * @param bool $agreement3
     */
    public function setAgreement3($agreement3)
    {
        $this->agreement3 = $agreement3;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface $updatedAt
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return CampaignPurchase[]
     */
    public function getCampaignPurchases(): array
    {
        return $this->campaignPurchases;
    }

    /**
     * @param CampaignPurchase[] $campaignPurchases
     */
    public function setCampaignPurchases($campaignPurchases): void
    {
        $this->campaignPurchases = $campaignPurchases;
    }

    /**
     * @param CampaignPurchase $campaignPurchase
     */
    public function addCampaignPurchase(CampaignPurchase $campaignPurchase): void
    {
        $this->campaignPurchases[] = $campaignPurchase;
    }

    /**
     * @param CampaignId $campaignId
     * @param Coupon     $coupon
     *
     * @return bool
     */
    public function canUsePurchase(CampaignId $campaignId, Coupon $coupon): bool
    {
        /** @var CampaignPurchase $purchase */
        foreach ($this->getPurchasesByCampaignId($campaignId) as $purchase) {
            if ($purchase->getCoupon()->getCode() === $coupon->getCode() &&
                $purchase->getCoupon()->getId() === $coupon->getId() &&
                $purchase->canBeUsed() === true
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CampaignId $campaignId
     * @param Coupon     $coupon
     *
     * @return bool
     */
    public function hasPurchased(CampaignId $campaignId, Coupon $coupon): bool
    {
        /** @var CampaignPurchase $purchase */
        foreach ($this->getPurchasesByCampaignId($campaignId) as $purchase) {
            if ($purchase->getCoupon()->getCode() === $coupon->getCode() &&
                $purchase->getCoupon()->getId() === $coupon->getId()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CampaignId $campaignId
     *
     * @return array
     */
    public function getPurchasesByCampaignId(CampaignId $campaignId): array
    {
        $tmp = [];
        foreach ($this->campaignPurchases as $campaignPurchase) {
            if ((string) $campaignPurchase->getCampaignId() === (string) $campaignId) {
                $tmp[] = $campaignPurchase;
            }
        }

        return $tmp;
    }

    /**
     * @param TransactionId $transactionId
     *
     * @return bool
     */
    public function hasTransactionId(TransactionId $transactionId): bool
    {
        foreach ($this->transactionIds as $id) {
            if ((string) $id === (string) $transactionId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TransactionId $transactionId
     */
    public function addTransactionId(TransactionId $transactionId): void
    {
        $this->transactionIds[] = $transactionId;
    }

    /**
     * @return int
     */
    public function getTransactionsCount(): int
    {
        return $this->transactionsCount;
    }

    /**
     * @param int $transactionsCount
     */
    public function setTransactionsCount(int $transactionsCount)
    {
        $this->transactionsCount = $transactionsCount;
    }

    /**
     * @return float
     */
    public function getTransactionsAmount(): float
    {
        return $this->transactionsAmount;
    }

    /**
     * @param float $transactionsAmount
     */
    public function setTransactionsAmount(float $transactionsAmount)
    {
        $this->transactionsAmount = $transactionsAmount;
    }

    /**
     * @return float
     */
    public function getTransactionsAmountWithoutDeliveryCosts(): float
    {
        return $this->transactionsAmountWithoutDeliveryCosts;
    }

    /**
     * @param float $transactionsAmountWithoutDeliveryCosts
     */
    public function setTransactionsAmountWithoutDeliveryCosts(float $transactionsAmountWithoutDeliveryCosts)
    {
        $this->transactionsAmountWithoutDeliveryCosts = $transactionsAmountWithoutDeliveryCosts;
    }

    /**
     * @return float
     */
    public function getAverageTransactionAmount(): float
    {
        return $this->averageTransactionAmount;
    }

    /**
     * @param float $averageTransactionAmount
     */
    public function setAverageTransactionAmount(float $averageTransactionAmount)
    {
        $this->averageTransactionAmount = $averageTransactionAmount;
    }

    /**
     * @return TransactionId[]
     */
    public function getTransactionIds(): array
    {
        return $this->transactionIds;
    }

    /**
     * @param TransactionId[] $transactionIds
     */
    public function setTransactionIds(array $transactionIds)
    {
        $this->transactionIds = $transactionIds;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTransactionDate(): ?\DateTime
    {
        return $this->lastTransactionDate;
    }

    /**
     * @param \DateTime $lastTransactionDate
     */
    public function setLastTransactionDate(\DateTime $lastTransactionDate)
    {
        $this->lastTransactionDate = $lastTransactionDate;
    }

    /**
     * @return float
     */
    public function getAmountExcludedForLevel(): float
    {
        return $this->amountExcludedForLevel;
    }

    /**
     * @param float $amountExcludedForLevel
     */
    public function setAmountExcludedForLevel(float $amountExcludedForLevel)
    {
        $this->amountExcludedForLevel = $amountExcludedForLevel;
    }

    /**
     * @return LevelDetails|null
     */
    public function getLevel(): ?LevelDetails
    {
        return $this->level;
    }

    /**
     * @param LevelDetails|null $level
     */
    public function setLevel(?LevelDetails $level)
    {
        $this->level = $level;
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
    public function setLastLevelRecalculation(\DateTime $lastLevelRecalculation = null): void
    {
        $this->lastLevelRecalculation = $lastLevelRecalculation;
    }

    /**
     * @return null|string
     */
    public function getAvatarPath(): ?string
    {
        return $this->avatarPath;
    }

    /**
     * @param null|string $avatarPath
     */
    public function setAvatarPath(?string $avatarPath): void
    {
        $this->avatarPath = $avatarPath;
    }

    /**
     * @return null|string
     */
    public function getAvatarOriginalName(): ?string
    {
        return $this->avatarOriginalName;
    }

    /**
     * @param null|string $avatarOriginalName
     */
    public function setAvatarOriginalName(?string $avatarOriginalName): void
    {
        $this->avatarOriginalName = $avatarOriginalName;
    }

    /**
     * @return null|string
     */
    public function getAvatarMime(): ?string
    {
        return $this->avatarMime;
    }

    /**
     * @param null|string $avatarMime
     */
    public function setAvatarMime(?string $avatarMime): void
    {
        $this->avatarMime = $avatarMime;
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
}
