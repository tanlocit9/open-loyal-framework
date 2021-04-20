<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenLoyalty\Bundle\PosBundle\DataFixtures\ORM\LoadPosData;
use OpenLoyalty\Bundle\SegmentBundle\DataFixtures\ORM\LoadSegmentData;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountSystemEvents;
use OpenLoyalty\Bundle\CampaignBundle\DataFixtures\ORM\LoadCampaignData;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;
use OpenLoyalty\Component\EarningRule\Domain\Command\CreateEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\Core\Domain\Model\SKU;
use OpenLoyalty\Component\EarningRule\Domain\PosId;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionSystemEvents;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;
use OpenLoyalty\Component\EarningRule\Domain\LevelId;

/**
 * Class LoadEarningRuleData.
 */
class LoadEarningRuleData extends ContainerAwareFixture implements FixtureInterface, OrderedFixtureInterface
{
    const QR_CODE_RULE_ID = 'e378c813-2116-448a-b125-564cef15f932';
    const GEO_RULE_ID = '00000001-0000-474c-b092-b0dd880c07e9';
    const EVENT_RULE_ID = '00000000-0000-474c-b092-b0dd880c07e3';
    const EVENT_RULE_ID_CUSTOMER_LOGGED_IN = '00000000-0000-474c-b092-b0dd880c07e9';
    const POINT_RULE_ID = '00000000-0000-474c-b092-b0dd880c07e4';
    const PURCHASE_RULE_ID = '00000000-0000-474c-b092-b0dd880c07e2';
    const MULTIPLY_RULE_ID = '00000000-0000-474c-b092-b0dd880c0723';
    const MULTIPLY_RULE_ID_BY_LABELS = '00000000-0000-474c-b092-b0dd880c0823';
    const NEWSLETTER_SUBSCRIPTION_RULE_ID = '00000000-0000-474c-b092-b0dd880c0725';
    const FACEBOOK_LIKE_RULE_ID = '00000000-0000-474c-b092-b0dd880c0121';
    const POINT_RULE_ID_WITH_POS = '00000000-0000-474c-b092-b0dd880c07e7';
    const EVENT_RULE_ID_WITH_POS = '00000000-0000-474c-b092-b0dd880c07e8';
    const EVENT_RULE_ID_FIRST_PURCHASE = '00000000-0000-474c-b092-b0dd990c07e3';
    const EVENT_RULE_ID_FIRST_PURCHASE_WITH_POST = '00000000-0000-474c-b092-b0dd770c07e3';
    const INSTANT_REWARD_RULE_ID = '4e7f7412-89bf-11e8-9a94-a6cf71072f73';
    const GENERAL_EARNING_RULE_WITH_SEGMENT_ID = '0e7f7412-89bf-11e8-9a94-a6cf71072f73';

    /**
     * @var array
     */
    private $earningRules = [
        self::EVENT_RULE_ID => [
            'type' => EarningRule::TYPE_EVENT,
            'data' => [
                'eventName' => AccountSystemEvents::ACCOUNT_CREATED,
                'pointsAmount' => 100,
                'name' => 'Event - Account Created - 100',
            ],
        ],
        self::EVENT_RULE_ID_FIRST_PURCHASE => [
            'type' => EarningRule::TYPE_EVENT,
            'data' => [
                'eventName' => TransactionSystemEvents::CUSTOMER_FIRST_TRANSACTION,
                'pointsAmount' => 10,
                'name' => 'Event - First Purchase - 10',
                'levels' => [
                    LoadLevelData::LEVEL0_ID,
                    LoadLevelData::LEVEL1_ID,
                    LoadLevelData::LEVEL2_ID,
                ],
            ],
        ],
        self::EVENT_RULE_ID_FIRST_PURCHASE_WITH_POST => [
            'type' => EarningRule::TYPE_EVENT,
            'data' => [
                'eventName' => TransactionSystemEvents::CUSTOMER_FIRST_TRANSACTION,
                'pointsAmount' => 12,
                'name' => 'Event - First Purchase - 12',
                'pos' => [LoadPosData::POS2_ID],
                'levels' => [
                    LoadLevelData::LEVEL0_ID,
                    LoadLevelData::LEVEL1_ID,
                    LoadLevelData::LEVEL2_ID,
                ],
            ],
        ],
        self::POINT_RULE_ID => [
            'type' => EarningRule::TYPE_POINTS,
            'data' => [
                'excludedSKUs' => ['123', '234', '567'],
                'pointValue' => 2.3,
                'name' => 'General spending rule - 2.3',
            ],
        ],
        self::PURCHASE_RULE_ID => [
            'type' => EarningRule::TYPE_PRODUCT_PURCHASE,
            'data' => [
                'skuIds' => ['ssku'],
                'pointsAmount' => 120,
                'name' => 'Product purchase earning rule - 120',
            ],
        ],
        self::MULTIPLY_RULE_ID => [
            'type' => EarningRule::TYPE_MULTIPLY_FOR_PRODUCT,
            'data' => [
                'skuIds' => ['SKU123'],
                'multiplier' => 2,
                'name' => 'Multiplier 2',
            ],
        ],

        self::NEWSLETTER_SUBSCRIPTION_RULE_ID => [
            'type' => EarningRule::TYPE_EVENT,
            'data' => [
                'eventName' => CustomerSystemEvents::NEWSLETTER_SUBSCRIPTION,
                'pointsAmount' => 85,
                'name' => 'Newsletter subscription test rule',
                'levels' => [LoadLevelData::LEVEL0_ID],
            ],
        ],
        self::FACEBOOK_LIKE_RULE_ID => [
            'type' => EarningRule::TYPE_CUSTOM_EVENT,
            'data' => [
                'eventName' => 'facebook_like',
                'pointsAmount' => 100,
                'name' => 'Facebook like test rule',
            ],
        ],
        self::POINT_RULE_ID_WITH_POS => [
            'type' => EarningRule::TYPE_POINTS,
            'data' => [
                'pointValue' => 2.3,
                'pos' => [LoadPosData::POS_ID],
                'name' => 'General spending rule limited to the POS',
                'levels' => [
                    LoadLevelData::LEVEL0_ID,
                    LoadLevelData::LEVEL1_ID,
                    LoadLevelData::LEVEL2_ID,
                ],
            ],
        ],
        self::EVENT_RULE_ID_WITH_POS => [
            'type' => EarningRule::TYPE_CUSTOM_EVENT,
            'data' => [
                'eventName' => 'test_event_limited_to_pos',
                'pointsAmount' => 88,
                'name' => 'Custom event - test event - 88 - limited to POS',
                'pos' => [LoadPosData::POS_ID],
            ],
        ],
        self::INSTANT_REWARD_RULE_ID => [
            'type' => EarningRule::TYPE_INSTANT_REWARD,
            'data' => [
                'target' => 'level',
                'active' => '1',
                'name' => 'Instant reward test rule',
                'allTimeActive' => true,
                'levels' => [
                    0 => LoadLevelData::LEVEL0_ID,
                    1 => LoadLevelData::LEVEL1_ID,
                    2 => LoadLevelData::LEVEL2_ID,
                ],
                'rewardCampaignId' => LoadCampaignData::PERCENTAGE_COUPON_CAMPAIGN_ID,
            ],
        ],
        self::GEO_RULE_ID => [
            'type' => EarningRule::TYPE_GEOLOCATION,
            'data' => [
                'target' => 'level',
                'active' => '1',
                'name' => 'Geo location test rule',
                'allTimeActive' => true,
                'levels' => [
                    0 => LoadLevelData::LEVEL0_ID,
                    1 => LoadLevelData::LEVEL1_ID,
                    2 => LoadLevelData::LEVEL2_ID,
                ],
                'latitude' => 50.00,
                'longitude' => 15,
                'radius' => 4.00,
                'pointsAmount' => 2,
            ],
        ],
        self::QR_CODE_RULE_ID => [
            'type' => EarningRule::TYPE_QRCODE,
            'data' => [
                'code' => 'qrcodeabcd',
                'active' => 1,
                'name' => 'Qr Code earning rule',
                'allTimeActive' => true,
                'pointsAmount' => 10,
                'target' => 'level',
                'levels' => [
                    0 => LoadLevelData::LEVEL0_ID,
                    1 => LoadLevelData::LEVEL1_ID,
                    2 => LoadLevelData::LEVEL2_ID,
                ],
            ],
        ],
        self::GENERAL_EARNING_RULE_WITH_SEGMENT_ID => [
            'type' => EarningRule::TYPE_POINTS,
            'data' => [
                'pointValue' => 100,
                'target' => 'level',
                'active' => '1',
                'name' => 'General spending rule with segment',
                'allTimeActive' => true,
                'segments' => [LoadSegmentData::SEGMENT11_ID],
                'levels' => [],
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->earningRules as $earningRuleId => &$earningRule) {
            // excluded skus
            if (array_key_exists('excludedSKUs', $earningRule['data'])) {
                $excludedSKUs = [];
                foreach ($earningRule['data']['excludedSKUs'] as $excludedSKU) {
                    $excludedSKUs[] = (new SKU($excludedSKU))->serialize();
                }
                $earningRule['data']['excludedSKUs'] = $excludedSKUs;
            }

            // levels
            if (array_key_exists('levels', $earningRule['data'])) {
                $levels = [];
                foreach ($earningRule['data']['levels'] as $level) {
                    $levels[] = new LevelId($level);
                }
                $earningRule['data']['levels'] = $levels;
            }

            // segments
            if (array_key_exists('segments', $earningRule['data'])) {
                $segments = [];
                foreach ($earningRule['data']['segments'] as $segment) {
                    $segments[] = new SegmentId($segment);
                }
                $earningRule['data']['segments'] = $segments;
            }

            // pos
            if (array_key_exists('pos', $earningRule['data'])) {
                $pos = [];
                foreach ($earningRule['data']['pos'] as $singlePos) {
                    $pos[] = new PosId($singlePos);
                }
                $earningRule['data']['pos'] = $pos;
            }

            $ruleData = array_merge($this->getMainData(), $earningRule['data']);

            if ($earningRule['type'] === EarningRule::TYPE_EVENT
                && $ruleData['eventName'] === AccountSystemEvents::ACCOUNT_CREATED
            ) {
                unset($ruleData['levels']);
            }

            $this->container->get('broadway.command_handling.command_bus')->dispatch(
                new CreateEarningRule(new EarningRuleId($earningRuleId), $earningRule['type'], $ruleData)
            );
        }
    }

    /**
     * @return array
     */
    protected function getMainData()
    {
        return [
            'description' => 'sth',
            'startAt' => (new \DateTime('-1 month'))->getTimestamp(),
            'endAt' => (new \DateTime('+1 month'))->getTimestamp(),
            'active' => true,
            'allTimeActive' => false,
            'levels' => ([new LevelId(LoadLevelData::LEVEL0_ID)]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
