<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use OpenLoyalty\Component\Campaign\Domain\Entity\CampaignPhoto;
use OpenLoyalty\Bundle\TranslationBundle\Model\FallbackTranslatable;
use OpenLoyalty\Component\Campaign\Domain\Model\CampaignActivity;
use OpenLoyalty\Component\Campaign\Domain\Model\CampaignFile;
use OpenLoyalty\Component\Campaign\Domain\Model\CampaignVisibility;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use Assert\Assertion as Assert;

/**
 * Class Campaign.
 */
class Campaign
{
    use FallbackTranslatable;

    const REWARD_TYPE_DISCOUNT_CODE = 'discount_code';
    const REWARD_TYPE_VALUE_CODE = 'value_code';
    const REWARD_TYPE_FREE_DELIVERY_CODE = 'free_delivery_code';
    const REWARD_TYPE_GIFT_CODE = 'gift_code';
    const REWARD_TYPE_EVENT_CODE = 'event_code';
    const REWARD_TYPE_CASHBACK = 'cashback';
    const REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE = 'percentage_discount_code';
    const REWARD_TYPE_CUSTOM_CAMPAIGN_CODE = 'custom_campaign_code';

    const MIN_TRANSACTION_PERCENTAGE_VALUE = 0;
    const MAX_TRANSACTION_PERCENTAGE_VALUE = 100;

    const CONNECT_TYPE_NONE = 'none';
    const CONNECT_TYPE_QRCODE_EARNING_RULE = 'qrcode';
    const CONNECT_TYPE_GEOLOCATION_EARNING_RULE = 'geolocation';

    /**
     * @var CampaignId
     */
    protected $campaignId;

    /**
     * @var string
     */
    protected $reward;

    /**
     * @var string
     */
    protected $moreInformationLink;

    /**
     * @var string|null
     */
    protected $pushNotificationText;

    /**
     * @var CampaignFile|null
     */
    protected $brandIcon;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * Cost of campaign reward – 0 for free or greater.
     *
     * @var float
     */
    protected $costInPoints = 0;

    /**
     * Cashback point value.
     *
     * @var float
     */
    protected $pointValue;

    /**
     * @var LevelId[]
     */
    protected $levels = [];

    /**
     * @var bool
     */
    protected $singleCoupon = false;

    /**
     * @var SegmentId[]
     */
    protected $segments = [];

    /**
     * @var bool
     */
    protected $unlimited = false;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $limitPerUser;

    /**
     * @var Coupon[]
     */
    protected $coupons;

    /**
     * @var CampaignActivity
     */
    protected $campaignActivity;

    /**
     * @var CampaignVisibility
     */
    protected $campaignVisibility;

    /**
     * @var string
     */
    protected $usageInstruction;

    /**
     * @var float
     */
    protected $rewardValue;

    /**
     * @var int
     */
    protected $tax;

    /**
     * @var float
     */
    protected $taxPriceValue;

    /**
     * @var Label[]
     */
    protected $labels = [];

    /**
     * @var int
     */
    protected $daysInactive;

    /**
     * @var int
     */
    protected $daysValid;

    /**
     * @var int
     */
    protected $transactionPercentageValue;

    /**
     * @var bool
     */
    protected $featured = false;

    /**
     * @var CampaignCategoryId[]
     */
    protected $categories = [];

    /**
     * @var CampaignPhoto[]
     */
    protected $photos = [];

    /**
     * @var bool
     */
    protected $public = false;

    /**
     * @var string|null
     */
    protected $connectType;

    /**
     * @var string|null
     */
    protected $earningRuleId;

    /**
     * @var bool
     */
    protected $fulfillmentTracking = false;

    /**
     * Campaign constructor.
     *
     * @param CampaignId|null $campaignId
     * @param array           $data
     */
    public function __construct(CampaignId $campaignId = null, array $data = [])
    {
        $this->campaignId = $campaignId;
        $this->setFromArray($data);
        $this->photos = new ArrayCollection();
    }

    /**
     * @param array $data
     */
    public function setFromArray(array $data)
    {
        if (isset($data['reward'])) {
            $this->reward = $data['reward'];
        }

        if (array_key_exists('moreInformationLink', $data)) {
            $this->moreInformationLink = $data['moreInformationLink'];
        }

        if (array_key_exists('pushNotificationText', $data)) {
            $this->pushNotificationText = $data['pushNotificationText'];
        }

        if (isset($data['active'])) {
            $this->active = $data['active'];
        }

        if ($this->reward === self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE) {
            if (isset($data['connectType'])) {
                $this->connectType = $data['connectType'];
            }
            if (isset($data['earningRuleId'])) {
                $this->earningRuleId = $data['earningRuleId'];
            } else {
                $this->earningRuleId = null;
            }
        } else {
            $this->connectType = null;
            $this->earningRuleId = null;
        }

        if ($this->reward !== self::REWARD_TYPE_CASHBACK && $this->reward !== self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE) {
            if (array_key_exists('daysInactive', $data)) {
                $this->setDaysInactive($data['daysInactive']);
            }

            if (array_key_exists('daysValid', $data)) {
                $this->setDaysValid($data['daysValid']);
            }
        }
        if ($this->reward === self::REWARD_TYPE_CASHBACK) {
            if (isset($data['coupons'])) {
                $this->coupons = $data['coupons'];
            }

            $this->setUnlimited(true);
            $this->setSingleCoupon(true);

            if (isset($data['pointValue'])) {
                $this->pointValue = $data['pointValue'];
            }
            $this->campaignVisibility = new CampaignVisibility(
                isset($data['campaignVisibility']['allTimeVisible']) ? $data['campaignVisibility']['allTimeVisible'] : true,
                isset($data['campaignVisibility']['visibleFrom']) ? $data['campaignVisibility']['visibleFrom'] : null,
                isset($data['campaignVisibility']['visibleTo']) ? $data['campaignVisibility']['visibleTo'] : null
            );
        } elseif ($this->reward === self::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE) {
            if (array_key_exists('transactionPercentageValue', $data)) {
                $this->setTransactionPercentageValue($data['transactionPercentageValue']);
            }
        } else {
            if (isset($data['costInPoints'])) {
                $this->costInPoints = $data['costInPoints'];
            }
            if (isset($data['unlimited'])) {
                $this->unlimited = $data['unlimited'];
            }
            if (isset($data['limit'])) {
                $this->limit = $data['limit'];
            }
            if (isset($data['limitPerUser'])) {
                $this->limitPerUser = $data['limitPerUser'];
            }

            if (isset($data['coupons'])) {
                $this->coupons = $data['coupons'];
            }
            if (isset($data['singleCoupon'])) {
                $this->singleCoupon = $data['singleCoupon'];
            }
            if (isset($data['campaignVisibility'])) {
                $this->campaignVisibility = new CampaignVisibility(
                    isset($data['campaignVisibility']['allTimeVisible']) ? $data['campaignVisibility']['allTimeVisible'] : true,
                    isset($data['campaignVisibility']['visibleFrom']) ? $data['campaignVisibility']['visibleFrom'] : null,
                    isset($data['campaignVisibility']['visibleTo']) ? $data['campaignVisibility']['visibleTo'] : null
                );
            }
        }

        if (isset($data['levels'])) {
            $this->levels = $data['levels'];
        }

        if (isset($data['segments'])) {
            $this->segments = $data['segments'];
        }

        if (isset($data['categories'])) {
            $this->categories = $data['categories'];
        }

        if (isset($data['campaignActivity'])) {
            $this->campaignActivity = new CampaignActivity(
                isset($data['campaignActivity']['allTimeActive']) ? $data['campaignActivity']['allTimeActive'] : true,
                isset($data['campaignActivity']['activeFrom']) ? $data['campaignActivity']['activeFrom'] : null,
                isset($data['campaignActivity']['activeTo']) ? $data['campaignActivity']['activeTo'] : null
            );
        }

        if (array_key_exists('rewardValue', $data)) {
            $this->setRewardValue($data['rewardValue']);
        }

        if (array_key_exists('tax', $data)) {
            $this->setTax($data['tax']);
        }

        if (array_key_exists('taxPriceValue', $data)) {
            $this->setTaxPriceValue($data['taxPriceValue']);
        }

        if (array_key_exists('featured', $data)) {
            $this->setFeatured($data['featured']);
        }

        if (array_key_exists('public', $data)) {
            $this->setPublic($data['public']);
        }

        if (array_key_exists('fulfillmentTracking', $data)) {
            $this->fulfillmentTracking = (bool) $data['fulfillmentTracking'];
        }

        if (array_key_exists('labels', $data)) {
            $labels = [];
            foreach ($data['labels'] as $label) {
                if ($label == null) {
                    continue;
                }
                $labels[] = Label::deserialize($label);
            }
            $this->labels = $labels;
        }

        if (array_key_exists('translations', $data)) {
            foreach ($data['translations'] as $locale => $transData) {
                if (array_key_exists('name', $transData)) {
                    $this->translate($locale, false)->setName($transData['name']);
                }
                if (array_key_exists('brandName', $transData)) {
                    $this->translate($locale, false)->setBrandName($transData['brandName']);
                }
                if (array_key_exists('shortDescription', $transData)) {
                    $this->translate($locale, false)->setShortDescription($transData['shortDescription']);
                }
                if (array_key_exists('conditionsDescription', $transData)) {
                    $this->translate($locale, false)->setConditionsDescription($transData['conditionsDescription']);
                }
                if (array_key_exists('usageInstruction', $transData)) {
                    $this->translate($locale, false)->setUsageInstruction($transData['usageInstruction']);
                }
                if (array_key_exists('brandDescription', $transData)) {
                    $this->translate($locale, false)->setBrandDescription($transData['brandDescription']);
                }
            }
            /** @var CampaignTranslation $translation */
            foreach ($this->getTranslations() as $translation) {
                if (!isset($data['translations'][$translation->getLocale()])) {
                    $this->removeTranslation($translation);
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isFulfillmentTracking(): bool
    {
        return $this->fulfillmentTracking;
    }

    /**
     * @param bool $fulfillmentTracking
     */
    public function setFulfillmentTracking(bool $fulfillmentTracking): void
    {
        $this->fulfillmentTracking = $fulfillmentTracking;
    }

    /**
     * @return CampaignId
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @return string
     */
    public function getReward()
    {
        return $this->reward;
    }

    /**
     * @param string $reward
     */
    public function setReward($reward)
    {
        $this->reward = $reward;
    }

    /**
     * @return string
     */
    public function getMoreInformationLink()
    {
        return $this->moreInformationLink;
    }

    /**
     * @param string $moreInformationLink
     */
    public function setMoreInformationLink($moreInformationLink)
    {
        $this->moreInformationLink = $moreInformationLink;
    }

    /**
     * @return string|null
     */
    public function getPushNotificationText(): ?string
    {
        return $this->pushNotificationText;
    }

    /**
     * @param string|null $pushNotificationText
     */
    public function setPushNotificationText(string $pushNotificationText = null)
    {
        $this->pushNotificationText = $pushNotificationText;
    }

    /**
     * @return bool
     */
    public function isActive()
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
     * @return float
     */
    public function getCostInPoints()
    {
        return round((float) $this->costInPoints, 2);
    }

    /**
     * @return bool
     */
    public function isSingleCoupon()
    {
        return $this->singleCoupon;
    }

    /**
     * @param bool $singleCoupon
     */
    public function setSingleCoupon($singleCoupon)
    {
        $this->singleCoupon = $singleCoupon;
    }

    /**
     * @param float $costInPoints
     */
    public function setCostInPoints($costInPoints)
    {
        $this->costInPoints = $costInPoints;
    }

    /**
     * @return LevelId[]
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @param LevelId[] $levels
     */
    public function setLevels($levels)
    {
        $this->levels = $levels;
    }

    /**
     * @return bool
     */
    public function hasSegments()
    {
        return !empty($this->segments);
    }

    /**
     * @return SegmentId[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param SegmentId[] $segments
     */
    public function setSegments($segments)
    {
        $this->segments = $segments;
    }

    /**
     * @return bool
     */
    public function isUnlimited()
    {
        return $this->unlimited;
    }

    /**
     * @param bool $unlimited
     */
    public function setUnlimited($unlimited)
    {
        $this->unlimited = $unlimited;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        if ($this->unlimited) {
            return;
        }

        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimitPerUser()
    {
        if ($this->unlimited) {
            return;
        }

        return $this->limitPerUser;
    }

    /**
     * @param int $limitPerUser
     */
    public function setLimitPerUser($limitPerUser)
    {
        $this->limitPerUser = $limitPerUser;
    }

    /**
     * @return Model\Coupon[]
     */
    public function getCoupons()
    {
        return $this->coupons;
    }

    /**
     * @param Model\Coupon[] $coupons
     */
    public function setCoupons($coupons)
    {
        $this->coupons = $coupons;
    }

    /**
     * @return CampaignActivity
     */
    public function getCampaignActivity()
    {
        return $this->campaignActivity;
    }

    /**
     * @param CampaignActivity $campaignActivity
     */
    public function setCampaignActivity($campaignActivity)
    {
        $this->campaignActivity = $campaignActivity;
    }

    /**
     * @return CampaignVisibility
     */
    public function getCampaignVisibility()
    {
        return $this->campaignVisibility;
    }

    /**
     * @param CampaignVisibility $campaignVisibility
     */
    public function setCampaignVisibility($campaignVisibility)
    {
        $this->campaignVisibility = $campaignVisibility;
    }

    /**
     * @param array $data
     *
     * @throws \Assert\AssertionFailedException
     */
    public static function validateRequiredData(array $data): void
    {
        Assert::keyIsset($data, 'reward');
        Assert::string($data['reward']);
        Assert::choice($data['reward'], [
            self::REWARD_TYPE_DISCOUNT_CODE,
            self::REWARD_TYPE_EVENT_CODE,
            self::REWARD_TYPE_FREE_DELIVERY_CODE,
            self::REWARD_TYPE_GIFT_CODE,
            self::REWARD_TYPE_VALUE_CODE,
            self::REWARD_TYPE_CASHBACK,
            self::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
            self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE,
        ]);
        Assert::keyIsset($data, 'levels');
        Assert::isArray($data['levels']);
        Assert::allIsInstanceOf($data['levels'], LevelId::class);
        Assert::keyIsset($data, 'segments');
        Assert::isArray($data['segments']);
        Assert::allIsInstanceOf($data['segments'], SegmentId::class);
        Assert::true(count($data['segments']) > 0 || count($data['levels']) > 0, 'There must be at least one level or one segment');

        if (!in_array($data['reward'], [self::REWARD_TYPE_CASHBACK, self::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE, self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE], true)) {
            if (!isset($data['unlimited']) || !$data['unlimited']) {
                Assert::keyIsset($data, 'limit');
                Assert::greaterOrEqualThan($data['limit'], 1);
                Assert::keyIsset($data, 'limitPerUser');
                Assert::greaterOrEqualThan($data['limitPerUser'], 1);
            }
            Assert::keyIsset($data, 'coupons');
            Assert::isArray($data['coupons']);
            Assert::allIsInstanceOf($data['coupons'], Coupon::class);
            Assert::keyIsset($data, 'campaignVisibility');
            CampaignVisibility::validateRequiredData($data['campaignVisibility']);
        }

        if ($data['reward'] !== self::REWARD_TYPE_CASHBACK && $data['reward'] !== self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE) {
            Assert::notBlank($data['daysInactive']);
            Assert::notBlank($data['daysValid']);
        }
        if ($data['reward'] == self::REWARD_TYPE_CASHBACK) {
            Assert::notBlank($data['pointValue']);
            Assert::greaterOrEqualThan($data['pointValue'], 0);
        }

        if ($data['reward'] === self::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE) {
            Assert::notBlank($data['transactionPercentageValue']);
            Assert::greaterOrEqualThan($data['transactionPercentageValue'], self::MIN_TRANSACTION_PERCENTAGE_VALUE);
            Assert::lessThan($data['transactionPercentageValue'], self::MAX_TRANSACTION_PERCENTAGE_VALUE);
        }

        if ($data['reward'] === self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE) {
            Assert::notBlank($data['connectType']);
            if ($data['connectType'] !== 'none') {
                Assert::keyIsset($data, 'earningRuleId');
            }
        }

        Assert::keyIsset($data, 'campaignActivity');
        CampaignActivity::validateRequiredData($data['campaignActivity']);
    }

    public function getFlatLevels()
    {
        return array_map(function (LevelId $levelId) {
            return $levelId->__toString();
        }, $this->levels);
    }

    public function getFlatSegments()
    {
        return array_map(function (SegmentId $segmentId) {
            return $segmentId->__toString();
        }, $this->segments);
    }

    public function getFlatCoupons()
    {
        return array_map(function (Coupon $coupon) {
            return $coupon->getCode();
        }, $this->coupons);
    }

    /**
     * @return array
     */
    public function getFlatCategories(): array
    {
        return array_map(function (CampaignCategoryId $campaignCategoryId) {
            return $campaignCategoryId->__toString();
        }, $this->categories);
    }

    /**
     * @return CampaignFile|null
     */
    public function getCampaignBrandIcon(): ?CampaignFile
    {
        return $this->brandIcon;
    }

    /**
     * @param CampaignFile|null $brandIcon
     */
    public function setCampaignBrandIcon(?CampaignFile $brandIcon)
    {
        $this->brandIcon = $brandIcon;
    }

    /**
     * @return bool
     */
    public function getBrandIcon(): bool
    {
        return $this->brandIcon->getPath() ? true : false;
    }

    /**
     * @return string|null
     */
    public function getBrandName(): ?string
    {
        return $this->translateFieldFallback(null, 'brandName')->getBrandName();
    }

    /**
     * @param string|null $brandName
     */
    public function setBrandName(?string $brandName): void
    {
        $this->translate(null, false)->setBrandName($brandName);
    }

    /**
     * @return float
     */
    public function getPointValue()
    {
        return $this->pointValue;
    }

    /**
     * @param float $pointValue
     */
    public function setPointValue($pointValue)
    {
        $this->pointValue = $pointValue;
    }

    /**
     * @return bool
     */
    public function isCashback(): bool
    {
        return $this->reward == self::REWARD_TYPE_CASHBACK;
    }

    /**
     * @return bool
     */
    public function isCustomReward(): bool
    {
        return $this->reward === self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE;
    }

    /**
     * @return bool
     */
    public function isPercentageDiscountCode(): bool
    {
        return $this->reward == self::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE;
    }

    /**
     * @return bool
     */
    public function canBeBoughtManually(): bool
    {
        return !$this->isCashback() && !$this->isCustomReward();
    }

    /**
     * @return bool
     */
    public function isTransactionRequired(): bool
    {
        return $this->isPercentageDiscountCode();
    }

    /**
     * @param float|null $rewardValue
     *
     * @return $this
     */
    public function setRewardValue($rewardValue)
    {
        if (null === $rewardValue) {
            $this->rewardValue = null;

            return $this;
        }

        $this->rewardValue = round((float) $rewardValue, 2);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getRewardValue()
    {
        return $this->rewardValue;
    }

    /**
     * @param int|null $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return int
     */
    public function getTax(): int
    {
        return (int) $this->tax;
    }

    /**
     * @param float $taxPriceValue
     */
    public function setTaxPriceValue($taxPriceValue)
    {
        $this->taxPriceValue = $taxPriceValue;
    }

    /**
     * @return float|null
     */
    public function getTaxPriceValue()
    {
        return $this->taxPriceValue;
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
    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * @param $pointsAmount
     *
     * @return float
     */
    public function calculateCashbackAmount($pointsAmount)
    {
        if (!$this->isCashback()) {
            return;
        }

        return round($pointsAmount * $this->getPointValue(), 2);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->translateFieldFallback(null, 'name')->getName();
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name)
    {
        $this->translate(null, false)->setName($name);
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->translateFieldFallback(null, 'shortDescription')->getShortDescription();
    }

    /**
     * @param null|string $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->translate(null, false)->setShortDescription($shortDescription);
    }

    /**
     * @return string|null
     */
    public function getConditionsDescription(): ?string
    {
        return $this->translateFieldFallback(null, 'conditionsDescription')->getConditionsDescription();
    }

    /**
     * @param null|string $conditionsDescription
     */
    public function setConditionsDescription(?string $conditionsDescription): void
    {
        $this->translate(null, false)->setConditionsDescription($conditionsDescription);
    }

    /**
     * @return string|null
     */
    public function getUsageInstruction(): ?string
    {
        return $this->translateFieldFallback(null, 'usageInstruction')->getUsageInstruction();
    }

    /**
     * @param null|string $usageInstruction
     */
    public function setUsageInstruction(?string $usageInstruction): void
    {
        $this->translate(null, false)->setUsageInstruction($usageInstruction);
    }

    /**
     * @return string|null
     */
    public function getBrandDescription(): ?string
    {
        return $this->translateFieldFallback(null, 'brandDescription')->getBrandDescription();
    }

    /**
     * @param string|null $brandDescription
     */
    public function setBrandDescription(?string $brandDescription): void
    {
        $this->translate(null, false)->setBrandDescription($brandDescription);
    }

    /**
     * @return int|null
     */
    public function getDaysInactive(): ?int
    {
        return $this->daysInactive;
    }

    /**
     * @param int $daysInactive
     */
    public function setDaysInactive(int $daysInactive): void
    {
        $this->daysInactive = $daysInactive;
    }

    /**
     * @return int|null
     */
    public function getDaysValid(): ?int
    {
        return $this->daysValid;
    }

    /**
     * @param int $daysValid
     */
    public function setDaysValid(int $daysValid): void
    {
        $this->daysValid = $daysValid;
    }

    /**
     * @return int|null
     */
    public function getTransactionPercentageValue(): ?int
    {
        return $this->transactionPercentageValue;
    }

    /**
     * @param int $transactionPercentageValue
     */
    public function setTransactionPercentageValue(int $transactionPercentageValue): void
    {
        $this->transactionPercentageValue = $transactionPercentageValue;
    }

    /**
     * @return CampaignPhoto[]
     */
    public function getPhotos(): array
    {
        return $this->photos;
    }

    /**
     * @return bool
     */
    public function isFeatured(): bool
    {
        return $this->featured;
    }

    /**
     * @param bool $featured
     */
    public function setFeatured(bool $featured): void
    {
        $this->featured = $featured;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    /**
     * @return CampaignCategoryId[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param CampaignCategoryId[] $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return string|null
     */
    public function getConnectType(): ?string
    {
        return $this->connectType;
    }

    /**
     * @param string|null $connectType
     */
    public function setConnectType(?string $connectType)
    {
        $this->connectType = $connectType;
    }

    /**
     * @return string|null
     */
    public function getEarningRuleId(): ?string
    {
        return $this->earningRuleId;
    }

    /**
     * @param string string|null
     */
    public function setEarningRuleId(?string $earningRuleId)
    {
        $this->earningRuleId = $earningRuleId;
    }
}
