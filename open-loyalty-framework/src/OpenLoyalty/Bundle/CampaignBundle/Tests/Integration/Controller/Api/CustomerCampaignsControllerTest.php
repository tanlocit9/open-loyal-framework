<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CampaignBundle\DataFixtures\ORM\LoadCampaignData;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\LevelId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Customer\Domain\CampaignId as CustomerCampaignId;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use Ramsey\Uuid\Uuid;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;

/**
 * Class CustomerCampaignsControllerTest.
 */
class CustomerCampaignsControllerTest extends BaseApiTest
{
    /**
     * @var CampaignRepository
     */
    protected $campaignRepository;

    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    protected function setUp()
    {
        parent::setUp();
        static::bootKernel();
        $this->campaignRepository = static::$kernel->getContainer()->get('oloy.campaign.repository');
        $this->customerDetailsRepository = static::$kernel->getContainer()->get('oloy.user.read_model.repository.customer_details');
    }

    /**
     * @test
     */
    public function it_allows_to_buy_a_campaign(): void
    {
        static::bootKernel();
        $customerDetailsBefore = $this->getCustomerDetails(LoadUserData::USER_USERNAME);
        $accountBefore = $this->getCustomerAccount(new CustomerId($customerDetailsBefore->getCustomerId()->__toString()));

        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/customer/campaign/'.LoadCampaignData::CAMPAIGN_ID.'/buy'
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('coupons', $data);
        $this->assertTrue(count($data['coupons']) == 1);
        $customerDetails = $this->getCustomerDetails(LoadUserData::USER_USERNAME);
        $this->assertInstanceOf(CustomerDetails::class, $customerDetails);
        $campaigns = $customerDetails->getCampaignPurchases();
        $found = false;
        foreach ($campaigns as $campaignPurchase) {
            if ((string) $campaignPurchase->getCampaignId() === LoadCampaignData::CAMPAIGN_ID) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Customer should have campaign purchase with campaign id = '.LoadCampaignData::CAMPAIGN_ID);

        $accountAfter = $this->getCustomerAccount(new CustomerId($customerDetails->getCustomerId()->__toString()));
        $this->assertTrue(
            ($accountBefore ? $accountBefore->getAvailableAmount() : 0) - 10 == ($accountAfter ? $accountAfter->getAvailableAmount() : 0),
            'Available points after campaign is bought should be '.(($accountBefore ? $accountBefore->getAvailableAmount() : 0) - 10)
            .', but it is '.($accountAfter ? $accountAfter->getAvailableAmount() : 0)
        );
    }

    /**
     * @test
     */
    public function it_not_allows_to_buy_a_campaign_for_customer_when_not_enough_points_and_quantity_more_than_one(): void
    {
        static::bootKernel();

        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/customer/campaign/'.LoadCampaignData::CAMPAIGN_ID.'/buy',
            [
                'quantity' => 100,
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 400');
    }

    /**
     * @test
     */
    public function it_allows_to_buy_a_campaign_and_properly_sets_active_dates(): void
    {
        static::bootKernel();
        $customerDetailsBefore = $this->getCustomerDetails(LoadUserData::USER_USERNAME);
        $accountBefore = $this->getCustomerAccount(new CustomerId($customerDetailsBefore->getCustomerId()->__toString()));

        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $date = new \DateTime();
        $activeSince = (clone $date)->modify('+10 days');
        $activeSince->setTime($activeSince->format('H'), $activeSince->format('i'), 0, 0);

        $activeTo = (clone $date)->modify('+30 days');
        $activeTo->setTime($activeTo->format('H'), $activeTo->format('i'), 0, 0);

        $client->request(
            'POST',
            '/api/customer/campaign/'.LoadCampaignData::INACTIVE_CAMPAIGN_ID.'/buy'
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('coupons', $data);
        $this->assertTrue(count($data['coupons']) == 1);
        $customerDetails = $this->getCustomerDetails(LoadUserData::USER_USERNAME);
        $this->assertInstanceOf(CustomerDetails::class, $customerDetails);
        $campaigns = $customerDetails->getCampaignPurchases();
        $found = null;
        foreach ($campaigns as $campaignPurchase) {
            if ($campaignPurchase->getCampaignId()->__toString() == LoadCampaignData::INACTIVE_CAMPAIGN_ID) {
                $found = $campaignPurchase;
                break;
            }
        }

        $this->assertInstanceOf(CampaignPurchase::class, $found, 'Customer should have campaign purchase with campaign id = '.LoadCampaignData::INACTIVE_CAMPAIGN_ID);
        $this->assertEquals(CampaignPurchase::STATUS_INACTIVE, $found->getStatus());
        $foundActiveSince = $found->getActiveSince();
        $foundActiveSince->setTime($foundActiveSince->format('H'), $foundActiveSince->format('i'), 0, 0);
        $this->assertEquals($activeSince, $foundActiveSince);

        $foundActiveTo = $found->getActiveTo();
        $foundActiveTo->setTime($foundActiveTo->format('H'), $foundActiveTo->format('i'), 0, 0);
        $this->assertEquals($activeTo, $foundActiveTo);

        $accountAfter = $this->getCustomerAccount(new CustomerId($customerDetails->getCustomerId()->__toString()));

        $beforeAmount = $accountBefore ? $accountBefore->getAvailableAmount() : 0;
        $afterAmount = $accountAfter ? $accountAfter->getAvailableAmount() : 0;

        $this->assertTrue(
            $beforeAmount - 5 == $afterAmount,
            'Available points after campaign is bought should be '.(($beforeAmount) - 5)
            .', but it is '.($afterAmount)
        );
    }

    /**
     * @test
     */
    public function it_returns_serialized_response_with_proper_fields(): void
    {
        static::bootKernel();
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/customer/campaign/bought'
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('campaigns', $data);
        $campaigns = $data['campaigns'];
        $this->assertGreaterThan(0, count($campaigns));
        $campaign = reset($campaigns);
        $this->assertArrayHasKey('purchaseAt', $campaign, 'Missing purchaseAt data');
        $this->assertArrayHasKey('costInPoints', $campaign, 'Missing costInPoints data');
        $this->assertArrayHasKey('campaignId', $campaign, 'Missing campaignID data');
        $this->assertInternalType('string', $campaign['campaignId'], 'Wrong campaignId type');
        $this->assertArrayHasKey('used', $campaign, 'Missing used data');
        $this->assertArrayHasKey('coupon', $campaign, 'Missing coupon data');
        $coupon = $campaign['coupon'];
        $this->assertArrayHasKey('code', $coupon, 'Missign coupon code value');
    }

    /**
     * @test
     */
    public function it_returns_serialized_response_with_proper_fields_and_includes_details(): void
    {
        static::bootKernel();
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/customer/campaign/bought',
            [
                'includeDetails' => 1,
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('campaigns', $data);
        $campaigns = $data['campaigns'];
        $this->assertGreaterThan(0, count($campaigns), 'No bought campaigns');
        $campaign = reset($campaigns);
        $this->assertArrayHasKey('campaign', $campaign, 'No campaigns details');
        $campaignDetails = $campaign['campaign'];
        $this->assertArrayHasKey('campaignId', $campaignDetails, 'Campaign details has no id');
    }

    /**
     * @test
     * @dataProvider sortParamsProvider
     */
    public function it_returns_available_campaigns_list_sorted($field, $direction, $oppositeDirection): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::TEST_USERNAME, LoadUserData::TEST_PASSWORD, 'customer');
        $client->request(
            'GET',
            sprintf('/api/customer/campaign/available?sort=%s&direction=%s', $field, $direction)
        );
        $sortedResponse = $client->getResponse();
        $sortedData = json_decode($sortedResponse->getContent(), true);
        $this->assertEquals(200, $sortedResponse->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('campaigns', $sortedData);

        if ($sortedData['total'] < 2) {
            return;
        }

        $firstElementSorted = reset($sortedData['campaigns']);
        $sortedSize = count($sortedData['campaigns']);

        $client = $this->createAuthenticatedClient(LoadUserData::TEST_USERNAME, LoadUserData::TEST_PASSWORD, 'customer');
        $client->request(
            'GET',
            sprintf('/api/customer/campaign/available?sort=%s&direction=%s', $field, $oppositeDirection)
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $firstElement = reset($data['campaigns']);
        $size = count($data['campaigns']);

        $this->assertNotEquals($firstElement['campaignId'], $firstElementSorted['campaignId']);
        $this->assertEquals($size, $sortedSize);
    }

    /**
     * @param bool $enabled
     *
     * @throws \Assert\AssertionFailedException
     */
    private function loadCashbackData(bool $enabled = false): void
    {
        $campaign = $this->campaignRepository->findOneBy([
            'campaignId' => new CampaignId(LoadCampaignData::CAMPAIGN3_ID),
        ]);

        if (!$campaign) {
            return;
        }
        if (!$enabled) {
            $campaign->setActive(false);
            $campaign->setUnlimited(false);
            $campaign->setCoupons([]);
            $campaign->setLevels(
                [
                    new LevelId(LoadLevelData::LEVEL2_ID),
                ]
            );
        } else {
            $campaign->setActive(true);
            $campaign->setUnlimited(true);
            $campaign->setCoupons([new Coupon('123', '123'), new Coupon('1233', '1233'), new Coupon('1234', '1234')]);
            $campaign->setLevels(
                [
                    new LevelId(LoadLevelData::LEVEL0_ID),
                    new LevelId(LoadLevelData::LEVEL1_ID),
                    new LevelId(LoadLevelData::LEVEL2_ID),
                    new LevelId(LoadLevelData::LEVEL3_ID),
                ]
            );
        }
        $this->campaignRepository->update($campaign);
    }

    /**
     * @test
     */
    public function it_available_campaigns_list_with_cashback(): void
    {
        $this->loadCashbackData(true);

        $client = $this->createAuthenticatedClient(LoadUserData::TEST_USERNAME, LoadUserData::TEST_PASSWORD, 'customer');
        $client->request('GET', '/api/customer/campaign/available');

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertOkResponseStatus($response);
        $this->assertArrayHasKey('campaigns', $content, 'Response should have campaigns');

        $data = $content['campaigns'];

        $cashbackCounter = 0;
        foreach ($data as $item) {
            if ($item['reward'] === Campaign::REWARD_TYPE_CASHBACK) {
                ++$cashbackCounter;
            }
        }

        $this->assertEquals(1, $cashbackCounter, 'Value should be 1');

        $this->loadCashbackData();
    }

    /**
     * @test
     * @dataProvider getCampaignsFilters
     *
     * @param array $filters
     * @param int   $expectedCount
     */
    public function it_returns_available_campaigns_filtered(array $filters, int $expectedCount): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::TEST_USERNAME, LoadUserData::TEST_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/customer/campaign/available',
            $filters
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('campaigns', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertCount($expectedCount, $data['campaigns']);
        $this->assertEquals($expectedCount, $data['total']);
    }

    /**
     * @return array
     */
    public function getCampaignsFilters(): array
    {
        return [
            [['isPublic' => 1], 1],
            [['isPublic' => 0], 0],
            [['isFeatured' => 1], 0],
            [['isFeatured' => 0], 1],
        ];
    }

    /**
     * @return array
     */
    public function sortParamsProvider(): array
    {
        return [
            ['campaignId', 'asc', 'desc'],
            ['name', 'asc', 'desc'],
            ['description', 'asc', 'desc'],
            ['reward', 'asc', 'desc'],
            ['active', 'asc', 'desc'],
            ['costInPoints', 'asc', 'desc'],
            ['hasPhoto', 'asc', 'desc'],
            ['usageLeft', 'asc', 'desc'],
            ['isPublic', 'asc', 'desc'],
        ];
    }

    /**
     * @test
     */
    public function it_returns_available_campaigns_list_filtered_by_segment_exclusiveness()
    {
        // exclusive
        $client = $this->createAuthenticatedClient(
            LoadUserData::TEST_USERNAME,
            LoadUserData::TEST_PASSWORD,
            'customer'
        );
        $client->request('GET', '/api/customer/campaign/available?hasSegment=1');
        $mustHaveSegmentResponse = $client->getResponse();
        $mustHaveSegmentData = json_decode($mustHaveSegmentResponse->getContent(), true);
        $this->assertEquals(200, $mustHaveSegmentResponse->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('campaigns', $mustHaveSegmentData);
        $mustHaveSegmentSize = count($mustHaveSegmentData['campaigns']);

        // assert no elements without segment are in response for segment-exclusive campaigns
        $elementsWithoutSegment = array_filter($mustHaveSegmentData['campaigns'], function ($campaign) {
            return empty($campaign['segmentNames']);
        });
        $this->assertEmpty($elementsWithoutSegment, 'Elements without segment present, asked for segment-exclusive campaigns');

        // non-exclusive
        $client = $this->createAuthenticatedClient(
            LoadUserData::TEST_USERNAME,
            LoadUserData::TEST_PASSWORD,
            'customer'
        );
        $client->request('GET', '/api/customer/campaign/available?hasSegment=0');
        $mustNotHaveSegmentResponse = $client->getResponse();
        $mustNotHaveSegmentData = json_decode($mustNotHaveSegmentResponse->getContent(), true);

        $this->assertArrayHasKey('campaigns', $mustNotHaveSegmentData);
        $mustNotHaveSegmentSize = count($mustNotHaveSegmentData['campaigns']);

        // assert no elements with segment are in response for non-exclusive campaigns
        $elementsWithSegment = array_filter($mustNotHaveSegmentData['campaigns'], function ($campaign) {
            return !empty($campaign['segmentNames']);
        });
        $this->assertEmpty($elementsWithSegment, 'Elements with segments present, asked for non-segment-exclusive campaigns');

        // all campaign data
        $client = $this->createAuthenticatedClient(
            LoadUserData::TEST_USERNAME,
            LoadUserData::TEST_PASSWORD,
            'customer'
        );
        $client->request('GET', '/api/customer/campaign/available');
        $allResponse = $client->getResponse();
        $allData = json_decode($allResponse->getContent(), true);

        $this->assertArrayHasKey('campaigns', $allData);
        $allSize = count($allData['campaigns']);

        // assert no data has been lost
        $this->assertEquals($mustHaveSegmentSize + $mustNotHaveSegmentSize, $allSize);
    }

    /**
     * @param CustomerId $customerId
     *
     * @return AccountDetails|null
     */
    protected function getCustomerAccount(CustomerId $customerId): AccountDetails
    {
        $accountDetailsRepository = static::$kernel->getContainer()->get('oloy.points.account.repository.account_details');
        $accounts = $accountDetailsRepository->findBy(['customerId' => $customerId->__toString()]);
        if (count($accounts) == 0) {
            return null;
        }

        return reset($accounts);
    }

    /**
     * @param $email
     *
     * @return CustomerDetails
     */
    protected function getCustomerDetails($email): CustomerDetails
    {
        $customerDetails = $this->customerDetailsRepository->findBy(['email' => $email]);
        /** @var CustomerDetails $customerDetails */
        $customerDetails = reset($customerDetails);

        return $customerDetails;
    }

    /**
     * @test
     */
    public function it_cannot_change_customer_coupon_to_used_when_not_active(): void
    {
        $customerDetails = $this->getCustomerDetails(LoadUserData::USER2_USERNAME);
        $couponId = Uuid::uuid4()->toString();
        $couponCode = Uuid::uuid4()->toString();
        $customerDetails->addCampaignPurchase(
            new CampaignPurchase(
                new \DateTime(),
                0,
                new CustomerCampaignId(LoadCampaignData::INACTIVE_CAMPAIGN_ID),
                new Coupon($couponId, $couponCode),
                Campaign::REWARD_TYPE_DISCOUNT_CODE,
                CampaignPurchase::STATUS_INACTIVE
            )
        );

        $this->customerDetailsRepository->save($customerDetails);

        $client = $this->createAuthenticatedClient(LoadUserData::USER2_USERNAME, LoadUserData::USER2_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/customer/campaign/coupons/mark_as_used',
            [
                'coupons' => [
                    [
                        'campaignId' => LoadCampaignData::CAMPAIGN_ID,
                        'code' => $couponCode,
                        'couponId' => $couponId,
                        'used' => true,
                    ],
                ],
            ]
        );

        $response = $client->getResponse();

        $customerDetails = $this->getCustomerDetails(LoadUserData::USER2_USERNAME);
        $campaigns = $customerDetails->getCampaignPurchases();
        $campaignPurchase = null;

        /** @var CampaignPurchase $campaign */
        foreach ($campaigns as $campaign) {
            if ($campaign->getCoupon()->getCode() === $couponCode && $campaign->getCoupon()->getId() === $couponId) {
                $campaignPurchase = $campaign;
            }
        }

        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 400');
        $this->assertNotNull($campaignPurchase);
        $this->assertInstanceOf(CampaignPurchase::class, $campaignPurchase);
        $this->assertTrue(!$campaignPurchase->isUsed());
    }

    /**
     * @test
     */
    public function it_change_multiple_customer_coupons_to_used(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/admin/customer/'.LoadUserData::USER2_USER_ID.'/campaign/'.LoadCampaignData::CAMPAIGN_ID.'/buy',
            [
                'withoutPoints' => true,
            ]
        );
        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);

        $client = $this->createAuthenticatedClient(LoadUserData::USER2_USERNAME, LoadUserData::USER2_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/customer/campaign/bought'
        );
        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);
        $data = json_decode($response->getContent(), true);
        $data = reset($data);
        $campaignBought = reset($data);
        $this->assertArrayHasKey('coupon', $campaignBought);
        $coupon = $campaignBought['coupon'];
        $this->assertArrayHasKey('id', $coupon);
        $this->assertArrayHasKey('code', $coupon);

        $couponId = $coupon['id'];
        $couponCode = $coupon['code'];

        $client = $this->createAuthenticatedClient(LoadUserData::USER2_USERNAME, LoadUserData::USER2_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/customer/campaign/coupons/mark_as_used',
            [
                'coupons' => [
                        [
                            'campaignId' => LoadCampaignData::CAMPAIGN_ID,
                            'code' => $couponCode,
                            'couponId' => $couponId,
                            'used' => true,
                        ],
                    ],
            ]
        );

        $response = $client->getResponse();

        $customerDetails = $this->getCustomerDetails(LoadUserData::USER2_USERNAME);
        $campaigns = $customerDetails->getCampaignPurchases();
        $campaignPurchase = null;

        /** @var CampaignPurchase $campaign */
        foreach ($campaigns as $campaign) {
            if ($campaign->getCoupon()->getCode() === $couponCode && $campaign->getCoupon()->getId() === $couponId) {
                $campaignPurchase = $campaign;
            }
        }

        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertNotNull($campaignPurchase);
        $this->assertInstanceOf(CampaignPurchase::class, $campaignPurchase);
        $this->assertTrue($campaignPurchase->isUsed());
    }
}
