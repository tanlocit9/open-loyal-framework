<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Component\Campaign\Tests\Unit\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\DeliveryStatus;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignShippingAddress;
use OpenLoyalty\Component\Campaign\Domain\TransactionId;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CampaignBoughtTest.
 */
class CampaignBoughtTest extends TestCase
{
    const CAMPAIGN_ID = '3a40b784-913f-45ee-8646-a78b2b4f5cef';
    const CUSTOMER_ID = '16d23cb7-e27a-47f7-a010-84f53b66cde1';
    const PURCHASED_AT = '2018-01-23 15:01';
    const COUPON_CODE = 'ABCD-4321';
    const COUPON_ID = '00000000-e27a-47f7-a010-84f53b66cde1';
    const CAMPAIGN_NAME = 'some-campaign';
    const CUSTOMER_EMAIL = 'user@oloy.com';
    const CUSTOMER_PHONE = '5551234554321';
    const CUSTOMER_NAME = 'Joe';
    const CUSTOMER_SURNAME = 'Doe';
    const COST_IN_POINTS = 100;
    const ACTIVE_POINTS = 940;
    const TAX_PRICE_VALUE = 23;
    const TRANSACTION_ID = '00000000-e27a-47f7-a010-84f53b660000';

    /**
     * @var Campaign
     */
    private $campaignObject;

    /**
     * @var CampaignBought
     */
    private $campaignBoughtObject;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        $campaignId = new CampaignId(self::CAMPAIGN_ID);
        $customerId = new CustomerId(self::CUSTOMER_ID);
        $this->campaignObject = new Campaign($campaignId);

        /** @var MockObject|CampaignShippingAddress $campaignShippingAddress */
        $campaignShippingAddress = $this->getMockBuilder(CampaignShippingAddress::class)
                                        ->disableOriginalConstructor()->getMock();

        $this->campaignBoughtObject = new CampaignBought(
            $campaignId,
            $customerId,
            new \DateTime(self::PURCHASED_AT),
            new Coupon(self::COUPON_CODE),
            'regular',
            self::CAMPAIGN_NAME,
            self::CUSTOMER_EMAIL,
            self::CUSTOMER_PHONE,
            $campaignShippingAddress,
            CampaignPurchase::STATUS_ACTIVE,
            false,
            self::CUSTOMER_NAME,
            self::CUSTOMER_SURNAME,
            self::COST_IN_POINTS,
            self::ACTIVE_POINTS,
            self::TAX_PRICE_VALUE,
            null,
            null,
            null,
            new DeliveryStatus()
        );
    }

    /**
     * @test
     */
    public function it_returns_right_interface(): void
    {
        $this->assertInstanceOf(SerializableReadModel::class, $this->campaignBoughtObject);
    }

    /**
     * @test
     */
    public function it_returns_generated_id_from_campaign_customer_and_level(): void
    {
        $this->assertEquals(
            CampaignBought::createId(
                new CampaignId(self::CAMPAIGN_ID),
                new CustomerId(self::CUSTOMER_ID),
                new Coupon(self::COUPON_CODE)
            ),
            $this->campaignBoughtObject->getId()
        );
    }

    /**
     * @throws \Assert\AssertionFailedException
     * @test
     */
    public function it_returns_generated_id_from_campaign_customer_coupon_code_coupon_id_transaction(): void
    {
        $campaignBoughtId = CampaignBought::createId(
            new CampaignId(self::CAMPAIGN_ID),
            new CustomerId(self::CUSTOMER_ID),
            new Coupon(self::COUPON_CODE, self::COUPON_ID),
            new TransactionId(self::TRANSACTION_ID)
        );

        $expectedId = self::CAMPAIGN_ID.'_'.self::CUSTOMER_ID.'_'.self::COUPON_CODE.'_'.self::TRANSACTION_ID.'_'.self::COUPON_ID;

        $this->assertSame($expectedId, $campaignBoughtId);
    }

    /**
     * @test
     */
    public function it_return_generated_id_from_campaign_customer_coupon_code(): void
    {
        $campaignBoughtId = CampaignBought::createId(
            new CampaignId(self::CAMPAIGN_ID),
            new CustomerId(self::CUSTOMER_ID),
            new Coupon(self::COUPON_CODE)
        );

        $expectedId = self::CAMPAIGN_ID.'_'.self::CUSTOMER_ID.'_'.self::COUPON_CODE;

        $this->assertSame($expectedId, $campaignBoughtId);
    }

    /**
     * @test
     */
    public function it_returns_same_data_from_serialization(): void
    {
        $serializedData = $this->campaignBoughtObject->serialize();

        $this->assertArrayHasKey('campaignId', $serializedData);
        $this->assertEquals(self::CAMPAIGN_ID, $serializedData['campaignId']);

        $this->assertArrayHasKey('customerId', $serializedData);
        $this->assertEquals(self::CUSTOMER_ID, $serializedData['customerId']);

        $this->assertArrayHasKey('purchasedAt', $serializedData);
        $this->assertEquals((new \DateTime(self::PURCHASED_AT))->getTimestamp(), $serializedData['purchasedAt']);

        $this->assertArrayHasKey('coupon', $serializedData);
        $this->assertEquals(self::COUPON_CODE, $serializedData['coupon']);

        $this->assertArrayHasKey('campaignType', $serializedData);
        $this->assertEquals('regular', $serializedData['campaignType']);

        $this->assertArrayHasKey('campaignName', $serializedData);
        $this->assertEquals(self::CAMPAIGN_NAME, $serializedData['campaignName']);

        $this->assertArrayHasKey('customerEmail', $serializedData);
        $this->assertEquals(self::CUSTOMER_EMAIL, $serializedData['customerEmail']);

        $this->assertArrayHasKey('customerPhone', $serializedData);
        $this->assertEquals(self::CUSTOMER_PHONE, $serializedData['customerPhone']);

        $this->assertArrayHasKey('used', $serializedData);
        $this->assertFalse($serializedData['used']);

        $this->assertArrayHasKey('customerName', $serializedData);
        $this->assertEquals(self::CUSTOMER_NAME, $serializedData['customerName']);

        $this->assertArrayHasKey('customerLastname', $serializedData);
        $this->assertEquals(self::CUSTOMER_SURNAME, $serializedData['customerLastname']);

        $this->assertArrayHasKey('costInPoints', $serializedData);
        $this->assertEquals(self::COST_IN_POINTS, $serializedData['costInPoints']);

        $this->assertArrayHasKey('currentPointsAmount', $serializedData);
        $this->assertEquals(self::ACTIVE_POINTS, $serializedData['currentPointsAmount']);

        $this->assertArrayHasKey('taxPriceValue', $serializedData);
        $this->assertEquals(self::TAX_PRICE_VALUE, $serializedData['taxPriceValue']);

        $this->assertArrayHasKey('deliveryStatus', $serializedData);
        $this->assertEquals('', $serializedData['deliveryStatus']);
    }

    /**
     * @test
     */
    public function it_returns_same_data_after_deserialization(): void
    {
        $deserializedObject = CampaignBought::deserialize(
            [
                'campaignId' => self::CAMPAIGN_ID,
                'customerId' => self::CUSTOMER_ID,
                'purchasedAt' => (new \DateTime(self::PURCHASED_AT))->getTimestamp(),
                'coupon' => self::COUPON_CODE,
                'couponId' => self::COUPON_ID,
                'campaignType' => 'regular',
                'campaignName' => self::CAMPAIGN_NAME,
                'customerEmail' => self::CUSTOMER_EMAIL,
                'customerPhone' => self::CUSTOMER_PHONE,
                'used' => false,
                'customerName' => self::CUSTOMER_NAME,
                'customerLastname' => self::CUSTOMER_SURNAME,
                'costInPoints' => self::COST_IN_POINTS,
                'currentPointsAmount' => self::ACTIVE_POINTS,
                'taxPriceValue' => self::TAX_PRICE_VALUE,
                'deliveryStatus' => 'ordered',
                'campaignShippingAddressStreet' => null,
                'campaignShippingAddressAddress1' => null,
                'campaignShippingAddressAddress2' => null,
                'campaignShippingAddressPostal' => null,
                'campaignShippingAddressCity' => null,
                'campaignShippingAddressProvince' => null,
                'campaignShippingAddressCountry' => null,
            ]
        );

        $serializedData = $deserializedObject->serialize();

        $this->assertArrayHasKey('campaignId', $serializedData);
        $this->assertEquals(self::CAMPAIGN_ID, $serializedData['campaignId']);

        $this->assertArrayHasKey('customerId', $serializedData);
        $this->assertEquals(self::CUSTOMER_ID, $serializedData['customerId']);

        $this->assertArrayHasKey('purchasedAt', $serializedData);
        $this->assertEquals((new \DateTime(self::PURCHASED_AT))->getTimestamp(), $serializedData['purchasedAt']);

        $this->assertArrayHasKey('coupon', $serializedData);
        $this->assertEquals(self::COUPON_CODE, $serializedData['coupon']);

        $this->assertArrayHasKey('couponId', $serializedData);
        $this->assertEquals(self::COUPON_ID, $serializedData['couponId']);

        $this->assertArrayHasKey('campaignType', $serializedData);
        $this->assertEquals('regular', $serializedData['campaignType']);

        $this->assertArrayHasKey('campaignName', $serializedData);
        $this->assertEquals(self::CAMPAIGN_NAME, $serializedData['campaignName']);

        $this->assertArrayHasKey('customerEmail', $serializedData);
        $this->assertEquals(self::CUSTOMER_EMAIL, $serializedData['customerEmail']);

        $this->assertArrayHasKey('customerPhone', $serializedData);
        $this->assertEquals(self::CUSTOMER_PHONE, $serializedData['customerPhone']);

        $this->assertArrayHasKey('used', $serializedData);
        $this->assertFalse($serializedData['used']);

        $this->assertArrayHasKey('customerName', $serializedData);
        $this->assertEquals(self::CUSTOMER_NAME, $serializedData['customerName']);

        $this->assertArrayHasKey('customerLastname', $serializedData);
        $this->assertEquals(self::CUSTOMER_SURNAME, $serializedData['customerLastname']);

        $this->assertArrayHasKey('costInPoints', $serializedData);
        $this->assertEquals(self::COST_IN_POINTS, $serializedData['costInPoints']);

        $this->assertArrayHasKey('currentPointsAmount', $serializedData);
        $this->assertEquals(self::ACTIVE_POINTS, $serializedData['currentPointsAmount']);

        $this->assertArrayHasKey('taxPriceValue', $serializedData);
        $this->assertEquals(self::TAX_PRICE_VALUE, $serializedData['taxPriceValue']);

        $this->assertArrayHasKey('deliveryStatus', $serializedData);
        $this->assertEquals('ordered', $serializedData['deliveryStatus']);
    }
}
