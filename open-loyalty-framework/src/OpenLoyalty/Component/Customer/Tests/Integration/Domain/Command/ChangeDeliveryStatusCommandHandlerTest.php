<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Integration\Domain\Command;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\Customer\Domain\Command\ChangeDeliveryStatusCommand;
use OpenLoyalty\Component\Customer\Domain\Command\ChangeDeliveryStatusCommandHandler;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ChangeDeliveryStatusCommandHandlerTest.
 */
final class ChangeDeliveryStatusCommandHandlerTest extends KernelTestCase
{
    private const CUSTOMER_ID = '00000000-0000-474c-b092-b0dd880c07aa';
    private const COUPON_ID = 'e6aa66ae-2d5f-403e-bc81-dba62928d364';

    /**
     * @var ChangeDeliveryStatusCommandHandler
     */
    private $handler;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var CampaignBoughtRepository
     */
    private $campaignBoughtRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->customerRepository = self::$kernel->getContainer()->get('oloy.user.customer.repository');
        $this->handler = new ChangeDeliveryStatusCommandHandler($this->customerRepository);
        $this->commandBus = self::$kernel->getContainer()->get('broadway.command_handling.simple_command_bus');
        $this->campaignBoughtRepository = self::$kernel->getContainer()->get(CampaignBoughtRepository::class);
        $this->addCampaignPurchase();
    }

    /**
     * @test
     */
    public function it_changes_campaign_bought_delivery_status(): void
    {
        $this->assertCampaignBoughtStatus('');

        $command = new ChangeDeliveryStatusCommand(self::COUPON_ID, self::CUSTOMER_ID, 'canceled');
        $this->commandBus->dispatch($command);

        $this->assertCampaignBoughtStatus('canceled');
    }

    /**
     * @param string $expectedStatus
     */
    private function assertCampaignBoughtStatus(string $expectedStatus): void
    {
        $campaignBought = $this->campaignBoughtRepository->findOneByCouponId(self::COUPON_ID);

        $this->assertSame($expectedStatus, (string) $campaignBought->getDeliveryStatus());
    }

    /**
     * Add campaign purchase.
     */
    private function addCampaignPurchase(): void
    {
        $data = [
            'campaignId' => '000096cf-32a3-43bd-9034-4df343e5fd94',
            'customerId' => self::CUSTOMER_ID,
            'transactionId' => '00000000-0000-1111-0000-000000002121',
            'purchasedAt' => (new \DateTime())->getTimestamp(),
            'coupon' => 'ABCD-12345',
            'couponId' => self::COUPON_ID,
            'campaignType' => 'discount_code',
            'campaignName' => 'Percentage discount code',
            'customerEmail' => 'user-temp@oloy.com',
            'customerPhone' => '+48345345000',
            'campaignShippingAddressStreet' => 'Example Street',
            'campaignShippingAddressAddress1' => '1',
            'campaignShippingAddressAddress2' => '2',
            'campaignShippingAddressPostal' => '00-000',
            'campaignShippingAddressCity' => 'Wroclaw',
            'campaignShippingAddressProvince' => 'Dolnoslaskie',
            'campaignShippingAddressCountry' => 'Poland',
            'used' => false,
        ];

        $this->campaignBoughtRepository->save(CampaignBought::deserialize($data));
    }
}
