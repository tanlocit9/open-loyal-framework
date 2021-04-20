<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Unit;

use OpenLoyalty\Bundle\CampaignBundle\Service\MultipleCampaignCouponUsageProvider;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Customer\Domain\CampaignId as CustomerCampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;

class MultipleCampaignCouponUsageProviderTest extends TestCase
{
    private const CUSTOMER_ID = '00000000-0000-0000-0000-000000000000';
    private const CAMPAIGN_ID = '00000000-0000-0000-0000-000000000001';
    private const COUPON_CODE = 'test';
    private const COUPON_ID = '00000000-0000-0000-0000-000000000002';

    /**
     * @var MultipleCampaignCouponUsageProvider
     */
    private $service;

    /**
     * @var CustomerDetails
     */
    private $customer;

    protected function setUp()
    {
        $customer = new CustomerDetails(new CustomerId(self::CUSTOMER_ID));
        $customer->addCampaignPurchase(new CampaignPurchase(
            new \DateTime(),
            1,
            new CustomerCampaignId(self::CAMPAIGN_ID),
            new Coupon(self::COUPON_ID, self::COUPON_CODE),
            Campaign::REWARD_TYPE_DISCOUNT_CODE
        ));
        $campaign = new Campaign(new CampaignId(self::CAMPAIGN_ID));

        $campaignRepository = $this->getMockBuilder(CampaignRepository::class)->getMock();
        $campaignRepository->method('byId')->willReturn($campaign);

        $customerRepository = $this->getMockBuilder(CustomerDetailsRepository::class)->getMock();
        $customerRepository->method('find')->willReturn($customer);

        $translator = $this->getMockBuilder(Translator::class)
            ->setConstructorArgs([
                'en',
            ])->getMock();
        $translator->method('trans')->willReturn('error');

        $this->service = new MultipleCampaignCouponUsageProvider(
            $campaignRepository,
            $customerRepository,
            $translator
        );
        $this->customer = $customer;
    }

    /**
     * @test
     */
    public function it_will_handle_change_campaigns_usage_request()
    {
        $result = $this->service->validateRequest(
            [
                'coupons' => [
                    'campaignId' => self::CAMPAIGN_ID,
                    'customerId' => self::CUSTOMER_ID,
                    'used' => true,
                    'code' => self::COUPON_CODE,
                    'couponId' => self::COUPON_ID,
                ],
            ]
        );

        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function it_will_handle_change_campaigns_for_customer_usage_request()
    {
        $result = $this->service->validateRequestForCustomer(
            [
                'coupons' => [
                    'campaignId' => self::CAMPAIGN_ID,
                    'used' => true,
                    'code' => self::COUPON_CODE,
                    'couponId' => self::COUPON_ID,
                ],
            ],
            $this->customer
        );

        $this->assertNotEmpty($result);
    }
}
