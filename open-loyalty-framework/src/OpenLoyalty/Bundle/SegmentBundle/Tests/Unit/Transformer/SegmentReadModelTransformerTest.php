<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Tests\Unit\Transformer;

use OpenLoyalty\Bundle\SegmentBundle\Model\Response\Criterion as ReadModelCriterion;
use OpenLoyalty\Bundle\SegmentBundle\Model\Response\Segment as ReadModelSegment;
use OpenLoyalty\Bundle\SegmentBundle\Provider\CustomerDetailsProviderInterface;
use OpenLoyalty\Bundle\SegmentBundle\Transformer\SegmentReadModelTransformer;
use OpenLoyalty\Component\Segment\Domain\CriterionId;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\Anniversary;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\AverageTransactionAmount;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\BoughtInPos;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\BoughtLabels;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\BoughtMakers;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\BoughtSKUs;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\CustomerHasLabels;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\CustomerList;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\CustomersWithLabelsValues;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\LastPurchaseNDaysBefore;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\PurchaseInPeriod;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\TransactionAmount;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\TransactionCount;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\TransactionPercentInPos;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use OpenLoyalty\Component\Segment\Domain\Model\CustomerDetails;
use OpenLoyalty\Component\Segment\Domain\Model\SegmentPart;
use OpenLoyalty\Component\Segment\Domain\PosId;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use OpenLoyalty\Component\Segment\Domain\SegmentPartId;
use PHPUnit\Framework\TestCase;

/**
 * Class SegmentReadModelTransformerTest.
 */
class SegmentReadModelTransformerTest extends TestCase
{
    private const SEGMENT_ID = '00000000-0000-0000-0000-000000000000';
    private const SEGMENT_NAME = 'segment_name';
    private const SEGMENT_DESCRIPTION = 'segment_desc';
    private const SEGMENT_ACTIVE = true;
    private const SEGMENT_CUSTOMERS_COUNT = 1;
    private const SEGMENT_PART_ID = '00000000-0000-0000-0000-000000000001';
    private const CRITERION_ID = '00000000-0000-0000-0000-000000000002';

    /**
     * @var SegmentReadModelTransformer
     */
    private $segmentReadModelTransformer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $customerDetailsProvider = $this->getMockBuilder(CustomerDetailsProviderInterface::class)->disableOriginalConstructor()->getMock();
        $customerDetailsProvider->method('getCustomers')->willReturn(
            [
                new CustomerDetails(
                    '00000000-0000-0000-0000-000000000003',
                    'email@test',
                    '+48111111111'
                ),
            ]
        );

        $this->segmentReadModelTransformer = new SegmentReadModelTransformer($customerDetailsProvider);
    }

    /**
     * @param ReadModelSegment $readModelSegment
     *
     * @return ReadModelCriterion
     */
    private function extractCriterionReadModel(ReadModelSegment $readModelSegment): ReadModelCriterion
    {
        $this->assertCount(1, $readModelSegment->getParts());
        $segmentPartReadModel = $readModelSegment->getParts()[0];

        $this->assertCount(1, $segmentPartReadModel->getCriteria());

        return $segmentPartReadModel->getCriteria()[0];
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_segment(Segment $segment): void
    {
        $readModel = $this->segmentReadModelTransformer->transform($segment);

        $this->assertEquals(self::SEGMENT_ID, $readModel->getSegmentId());
        $this->assertEquals(self::SEGMENT_NAME, $readModel->getName());
        $this->assertEquals(self::SEGMENT_DESCRIPTION, $readModel->getDescription());
        $this->assertEquals(self::SEGMENT_CUSTOMERS_COUNT, $readModel->getCustomersCount());
        $this->assertEquals(self::SEGMENT_ACTIVE, $readModel->isActive());
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_segment_part(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);

        $this->assertCount(1, $readModel->getParts());

        $segmentPartReadModel = $readModel->getParts()[0];

        $this->assertEquals(self::SEGMENT_PART_ID, $segmentPartReadModel->getSegmentPartId());
        $this->assertEmpty($segmentPartReadModel->getCriteria());
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_anniversary(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $anniversary = new Anniversary(new CriterionId(self::CRITERION_ID));
        $anniversary->setAnniversaryType(Anniversary::TYPE_BIRTHDAY);
        $anniversary->setDays(1);

        $segmentPart->setCriteria([$anniversary]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $anniversaryReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_ANNIVERSARY, $anniversaryReadModel->getType());
        $this->assertEquals($anniversary->getDays(), $anniversaryReadModel->getData()['days']);
        $this->assertEquals($anniversary->getAnniversaryType(), $anniversaryReadModel->getData()['anniversaryType']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_average_transaction_amount(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $averageTransactionAmount = new AverageTransactionAmount(new CriterionId(self::CRITERION_ID));
        $averageTransactionAmount->setFromAmount(1);
        $averageTransactionAmount->setToAmount(10);

        $segmentPart->setCriteria([$averageTransactionAmount]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $averageTransactionAmountReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_AVERAGE_TRANSACTION_AMOUNT, $averageTransactionAmountReadModel->getType());
        $this->assertEquals($averageTransactionAmount->getFromAmount(), $averageTransactionAmountReadModel->getData()['fromAmount']);
        $this->assertEquals($averageTransactionAmount->getToAmount(), $averageTransactionAmountReadModel->getData()['toAmount']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_bought_in_pos(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $boughtInPos = new BoughtInPos(new CriterionId(self::CRITERION_ID));
        $boughtInPos->setPosIds(['00000000-0000-0000-0000-000000000003']);

        $segmentPart->setCriteria([$boughtInPos]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $boughtInPosReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_BOUGHT_IN_POS, $boughtInPosReadModel->getType());
        $this->assertNotEmpty($boughtInPosReadModel->getData()['posIds']);
        $this->assertCount(1, $boughtInPosReadModel->getData()['posIds']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_bought_labels(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $boughtLabels = new BoughtLabels(new CriterionId(self::CRITERION_ID));
        $boughtLabels->setLabels(['00000000-0000-0000-0000-000000000003']);

        $segmentPart->setCriteria([$boughtLabels]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $boughtLabelsReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_BOUGHT_LABELS, $boughtLabelsReadModel->getType());
        $this->assertNotEmpty($boughtLabelsReadModel->getData()['labels']);
        $this->assertCount(1, $boughtLabelsReadModel->getData()['labels']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_bought_makers(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $boughtMakers = new BoughtMakers(new CriterionId(self::CRITERION_ID));
        $boughtMakers->setMakers(['00000000-0000-0000-0000-000000000003']);

        $segmentPart->setCriteria([$boughtMakers]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $boughtMakersReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_BOUGHT_MAKERS, $boughtMakersReadModel->getType());
        $this->assertNotEmpty($boughtMakersReadModel->getData()['makers']);
        $this->assertCount(1, $boughtMakersReadModel->getData()['makers']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_bought_skus(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $boughtSKUs = new BoughtSKUs(new CriterionId(self::CRITERION_ID));
        $boughtSKUs->setSkuIds(['00000000-0000-0000-0000-000000000003']);

        $segmentPart->setCriteria([$boughtSKUs]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $boughtSKUsReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_BOUGHT_SKUS, $boughtSKUsReadModel->getType());
        $this->assertNotEmpty($boughtSKUsReadModel->getData()['skuIds']);
        $this->assertCount(1, $boughtSKUsReadModel->getData()['skuIds']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_customers_has_labels(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $customerHasLabels = new CustomerHasLabels(new CriterionId(self::CRITERION_ID));
        $customerHasLabels->setLabels(['00000000-0000-0000-0000-000000000003']);

        $segmentPart->setCriteria([$customerHasLabels]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $customerHasLabelsReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_CUSTOMER_HAS_LABELS, $customerHasLabelsReadModel->getType());
        $this->assertNotEmpty($customerHasLabelsReadModel->getData()['labels']);
        $this->assertCount(1, $customerHasLabelsReadModel->getData()['labels']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_customers_list(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $customerList = new CustomerList(new CriterionId(self::CRITERION_ID));
        $customerList->setCustomers(['00000000-0000-0000-0000-000000000003']);

        $segmentPart->setCriteria([$customerList]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $customerListReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_CUSTOMER_LIST, $customerListReadModel->getType());
        $this->assertNotEmpty($customerListReadModel->getData()['customers']);
        $this->assertCount(1, $customerListReadModel->getData()['customers']);
        $this->assertCount(1, $customerListReadModel->getData()['segmentedCustomers']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_customers_with_labels_values(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $customersWithLabelsValues = new CustomersWithLabelsValues(new CriterionId(self::CRITERION_ID));
        $customersWithLabelsValues->setLabels(['00000000-0000-0000-0000-000000000003']);

        $segmentPart->setCriteria([$customersWithLabelsValues]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $customersWithLabelsValuesReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_CUSTOMER_WITH_LABELS_VALUES, $customersWithLabelsValuesReadModel->getType());
        $this->assertNotEmpty($customersWithLabelsValuesReadModel->getData()['labels']);
        $this->assertCount(1, $customersWithLabelsValuesReadModel->getData()['labels']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_last_purchase_n_days_before(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $lastPurchaseNDaysBefore = new LastPurchaseNDaysBefore(new CriterionId(self::CRITERION_ID));
        $lastPurchaseNDaysBefore->setDays(1);

        $segmentPart->setCriteria([$lastPurchaseNDaysBefore]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $lastPurchaseNDaysBeforeReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_LAST_PURCHASE_N_DAYS_BEFORE, $lastPurchaseNDaysBeforeReadModel->getType());
        $this->assertEquals(1, $lastPurchaseNDaysBeforeReadModel->getData()['days']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_purchase_in_period(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $purchaseInPeriod = new PurchaseInPeriod(new CriterionId(self::CRITERION_ID));
        $fromDate = new \DateTime('2018-01-01 11:11:11');
        $toDate = new \DateTime('2019-01-01 11:11:11');
        $purchaseInPeriod->setFromDate($fromDate);
        $purchaseInPeriod->setToDate($toDate);

        $segmentPart->setCriteria([$purchaseInPeriod]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $purchaseInPeriodReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_PURCHASE_PERIOD, $purchaseInPeriodReadModel->getType());
        $this->assertEquals($fromDate->getTimestamp(), $purchaseInPeriodReadModel->getData()['fromDate']->getTimestamp());
        $this->assertEquals($toDate->getTimestamp(), $purchaseInPeriodReadModel->getData()['toDate']->getTimestamp());
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_transaction_amount(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $transactionAmount = new TransactionAmount(new CriterionId(self::CRITERION_ID));
        $transactionAmount->setFromAmount(1);
        $transactionAmount->setToAmount(500);

        $segmentPart->setCriteria([$transactionAmount]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $transactionAmountReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_TRANSACTION_AMOUNT, $transactionAmountReadModel->getType());
        $this->assertEquals(1, $transactionAmountReadModel->getData()['fromAmount']);
        $this->assertEquals(500, $transactionAmountReadModel->getData()['toAmount']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_transaction_count(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $transactionCount = new TransactionCount(new CriterionId(self::CRITERION_ID));
        $transactionCount->setMin(1);
        $transactionCount->setMax(500);

        $segmentPart->setCriteria([$transactionCount]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $transactionCountReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_TRANSACTION_COUNT, $transactionCountReadModel->getType());
        $this->assertEquals(1, $transactionCountReadModel->getData()['min']);
        $this->assertEquals(500, $transactionCountReadModel->getData()['max']);
    }

    /**
     * @dataProvider getSegment
     * @test
     *
     * @param Segment $segment
     */
    public function it_should_transform_transaction_percentage_in_pos(Segment $segment): void
    {
        $segmentPart = new SegmentPart(new SegmentPartId(self::SEGMENT_PART_ID));

        $transactionPercentInPos = new TransactionPercentInPos(new CriterionId(self::CRITERION_ID));
        $transactionPercentInPos->setPercent(10);
        $transactionPercentInPos->setPosId(new PosId('00000000-0000-0000-0000-000000000003'));

        $segmentPart->setCriteria([$transactionPercentInPos]);
        $segment->setParts([$segmentPart]);

        $readModel = $this->segmentReadModelTransformer->transform($segment);
        $transactionPercentInPosReadModel = $this->extractCriterionReadModel($readModel);

        $this->assertEquals(Criterion::TYPE_TRANSACTION_PERCENT_IN_POS, $transactionPercentInPosReadModel->getType());
        $this->assertEquals(10, $transactionPercentInPosReadModel->getData()['percent']);
        $this->assertEquals('00000000-0000-0000-0000-000000000003', $transactionPercentInPosReadModel->getData()['posId']);
    }

    /**
     * @return array
     */
    public function getSegment(): array
    {
        $segment = new Segment(
            new SegmentId(self::SEGMENT_ID),
            self::SEGMENT_NAME,
            self::SEGMENT_DESCRIPTION
        );

        $segment->setActive(self::SEGMENT_ACTIVE);
        $segment->setCustomersCount(self::SEGMENT_CUSTOMERS_COUNT);

        return [
            [
                $segment,
            ],
        ];
    }
}
