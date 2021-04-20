<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Tests\Unit\Service;

use Faker\Provider\Uuid;
use OpenLoyalty\Bundle\CoreBundle\CSVGenerator\Mapper;
use OpenLoyalty\Bundle\CoreBundle\Service\CSVGenerator;
use OpenLoyalty\Bundle\CoreBundle\Service\GeneratorInterface;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignShippingAddress;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * Class CSVGeneratorTest.
 */
final class CSVGeneratorTest extends TestCase
{
    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var array
     */
    private $headers = [
        'Name',
        'Date',
        'Cost',
        'Tax value',
        'email',
        'phone',
        'Firstname',
        'Surname',
        'Points balance',
        'Is used',
    ];

    /**
     * @var array
     */
    private $fields = [
        'campaignName',
        'purchasedAt',
        'costInPoints',
        'taxValue',
        'customerEmail',
        'customerPhone',
        'customerName',
        'customerLastname',
        'currentPointsAmount',
        'used',
    ];

    /**
     * @var
     */
    private $rows;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        /** @var MockObject|PropertyAccessor $propertyAccessor */
        $propertyAccessor = $this->getMockBuilder(PropertyAccessor::class)->disableOriginalConstructor()->getMock();
        /** @var MockObject|Mapper $mapper */
        $mapper = $this->getMockBuilder(Mapper::class)->setMethods(['create'])->setConstructorArgs([[]])->getMock();
        $this->generator = new CSVGenerator($propertyAccessor, $mapper);
        $this->serializer = new Serializer([], [new CsvEncoder()]);

        /** @var MockObject|CampaignShippingAddress $campaignShippingAddress */
        $campaignShippingAddress = $this->getMockBuilder(CampaignShippingAddress::class)
            ->disableOriginalConstructor()->getMock();

        $campaign1 = new CampaignBought(
            new CampaignId(Uuid::uuid()),
            new CustomerId(Uuid::uuid()),
            new \DateTime('now'),
            new Coupon('1234'),
            'coupon_code',
            'Some Campaign',
            'some@email.com',
            '+4894949494',
            $campaignShippingAddress,
            CampaignPurchase::STATUS_ACTIVE,
            false,
            'Joe',
            'Doe',
            99,
            1902
        );

        $campaign2 = new CampaignBought(
            new CampaignId(Uuid::uuid()),
            new CustomerId(Uuid::uuid()),
            new \DateTime('-1 day'),
            new Coupon('4321'),
            'coupon_code',
            'Some Campaign 2',
            'fake@email.com',
            '+449393939',
            $campaignShippingAddress,
            CampaignPurchase::STATUS_ACTIVE,
            false,
            'Alice',
            'Wonderland',
            100,
            980
        );

        $this->rows[] = $campaign1;
        $this->rows[] = $campaign2;
    }

    /**
     * @test
     */
    public function it_has_right_interface_implemented(): void
    {
        $this->assertInstanceOf(GeneratorInterface::class, $this->generator);
    }

    /**
     * @test
     */
    public function it_returns_right_data(): void
    {
        $result = $this->generator->generate($this->rows, $this->headers, $this->fields);
        $rows = $this->serializer->decode($result, 'csv');
        $this->assertCount(2, $rows);

        $this->assertEquals('Joe', $rows[0][6]['Firstname']);
        $this->assertEquals('+4894949494', $rows[0][5]['phone']);
        $this->assertInternalType('string', $rows[0][1]['Date']);
        $this->assertEquals('Alice', $rows[1][6]['Firstname']);
        $this->assertEquals('+449393939', $rows[1][5]['phone']);
        $this->assertInternalType('string', $rows[1][1]['Date']);
    }

    /**
     * @test
     */
    public function it_returns_only_heading_with_no_data(): void
    {
        $result = $this->generator->generate([[]], $this->headers, $this->fields);
        $rows = $this->serializer->decode($result, 'csv');
        $this->assertEquals(1, count($rows));
    }
}
