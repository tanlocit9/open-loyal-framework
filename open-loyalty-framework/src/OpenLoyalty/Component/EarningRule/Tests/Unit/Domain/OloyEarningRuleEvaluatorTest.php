<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\EarningRule\Tests\Unit\Domain;

use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountSystemEvents;
use OpenLoyalty\Component\Account\Domain\TransactionId;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Core\Domain\Model\LabelMultiplier;
use OpenLoyalty\Component\Core\Domain\Model\SKU;
use OpenLoyalty\Component\Customer\Domain\Model\Status;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetailsRepository;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\EarningRuleAlgorithmFactoryInterface;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\MultiplyPointsByProductLabelsRuleAlgorithm;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\MultiplyPointsForProductRuleAlgorithm;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\PointsEarningRuleAlgorithm;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\ProductPurchaseEarningRuleAlgorithm;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleGeoRepository;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleQrcodeRepository;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\EarningRule\Domain\EventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\MultiplyPointsByProductLabelsEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\MultiplyPointsForProductEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\OloyEarningRuleEvaluator;
use OpenLoyalty\Component\EarningRule\Domain\PointsEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\PosId;
use OpenLoyalty\Component\EarningRule\Domain\ProductPurchaseEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableProvider;
use OpenLoyalty\Component\Segment\Domain\ReadModel\SegmentedCustomersRepository;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionSystemEvents;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class OloyEarningRuleEvaluatorTest.
 */
final class OloyEarningRuleEvaluatorTest extends TestCase
{
    private const USER_ID = '00000000-0000-0000-0000-000000000000';

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_rule(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(608, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_rule_and_excluded_sku(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludedSKUs([new SKU('000')]);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(208, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_rule_and_excluded_label(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setLabelsInclusionType(PointsEarningRule::LABELS_INCLUSION_TYPE_EXCLUDE);
        $pointsEarningRule->setExcludedLabels([new Label('color', 'red')]);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(560, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_rule_and_included_label(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setLabelsInclusionType(PointsEarningRule::LABELS_INCLUSION_TYPE_INCLUDE);
        $pointsEarningRule->setExcludedLabels([new Label('color', 'red')]); // should be skipped due to inclusion type
        $pointsEarningRule->setIncludedLabels([new Label('color', 'red')]);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(48, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_rule_without_delivery_costs(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setLabelsInclusionType(PointsEarningRule::LABELS_INCLUSION_TYPE_EXCLUDE);
        $pointsEarningRule->setExcludedLabels([new Label('color', 'red')]);
        $pointsEarningRule->setExcludeDeliveryCost(true);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(400, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_sku_rule(): void
    {
        $purchaseEarningRule = new ProductPurchaseEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $purchaseEarningRule->setSkuIds(['000']);
        $purchaseEarningRule->setPointsAmount(200);

        $evaluator = $this->getEarningRuleEvaluator([$purchaseEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(200, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_if_there_are_more_rules(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(10);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $pointsEarningRule2 = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule2->setPointValue(4);
        $pointsEarningRule2->setExcludeDeliveryCost(false);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule, $pointsEarningRule2]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(2128, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_if_there_are_more_rule_types(): void
    {
        $purchaseEarningRule = new ProductPurchaseEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $purchaseEarningRule->setSkuIds(['123']);
        $purchaseEarningRule->setPointsAmount(100);

        $pointsEarningRule2 = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule2->setPointValue(4);
        $pointsEarningRule2->setExcludeDeliveryCost(false);

        $evaluator = $this->getEarningRuleEvaluator([$purchaseEarningRule, $pointsEarningRule2]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(708, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_event_account_created(): void
    {
        $eventEarningRule = new EventEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $eventEarningRule->setEventName(AccountSystemEvents::ACCOUNT_CREATED);
        $eventEarningRule->setPointsAmount(200);

        $evaluator = $this->getEarningRuleEvaluator([$eventEarningRule]);
        $customerId = '11';
        $points = $evaluator->evaluateEvent(AccountSystemEvents::ACCOUNT_CREATED, $customerId);
        $this->assertEquals(200, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_event_first_purchase(): void
    {
        $eventEarningRule = new EventEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $eventEarningRule->setEventName(TransactionSystemEvents::CUSTOMER_FIRST_TRANSACTION);
        $eventEarningRule->setPointsAmount(56);
        $eventEarningRule->setPos([new PosId('00000000-0000-474c-1111-b0dd880c07e2')]);

        $evaluator = $this->getEarningRuleEvaluator([$eventEarningRule]);
        $customerId = '11';
        $points = $evaluator->evaluateEvent(TransactionSystemEvents::CUSTOMER_FIRST_TRANSACTION, $customerId);
        $this->assertEquals(56, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_rule_if_excluded_label(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);
        $pointsEarningRule->setLabelsInclusionType(PointsEarningRule::LABELS_INCLUSION_TYPE_EXCLUDE);
        $pointsEarningRule->setExcludedLabels([new Label('color', 'red')]);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(560, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_rule_if_excluded_sku(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);
        $pointsEarningRule->setExcludedSKUs([new SKU('000')]);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(208, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_multiply_points_rule_by_sku(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $multiplyPointsEarningRule = new MultiplyPointsForProductEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $multiplyPointsEarningRule->setMultiplier(3);
        $multiplyPointsEarningRule->setSkuIds([new SKU('123')]);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule, $multiplyPointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(704, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_multiply_points_rule_by_label(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $multiplyPointsEarningRule = new MultiplyPointsForProductEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $multiplyPointsEarningRule->setMultiplier(3);
        $multiplyPointsEarningRule->setLabels([new Label('color', 'red')]);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule, $multiplyPointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(704, $points);
    }

    public function productLabelMultipliersProvider(): array
    {
        return [
            [[new LabelMultiplier('color', 'red', 3)], 704],
            [
                [
                    new LabelMultiplier('color', 'red', 3),
                    new LabelMultiplier('color', 'blue', 6),
                ],
                2704,
            ],
            [
                [
                    new LabelMultiplier('color', 'red', 0),
                    new LabelMultiplier('color', 'blue', 2),
                    new LabelMultiplier('size', 'xxl', 2),
                ],
                960,
            ],
            [
                [
                    new LabelMultiplier('color', 'red', 0),
                    new LabelMultiplier('color', 'blue', 0),
                ],
                160,
            ],
            [
                [
                    new LabelMultiplier('color', 'blue', 2),
                    new LabelMultiplier('color', 'orange', 3),
                ],
                1008,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider productLabelMultipliersProvider
     *
     * @param array $labelMultipliers
     * @param int   $expectedPoints
     */
    public function it_returns_proper_value_for_given_transaction_and_multiply_points_rule_by_label_multipliers(
        array $labelMultipliers,
        int $expectedPoints
    ): void {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $multiplyPointsEarningRule = new MultiplyPointsByProductLabelsEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $multiplyPointsEarningRule->setLabelMultipliers($labelMultipliers);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule, $multiplyPointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals($expectedPoints, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_with_above_minimal(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);
        $pointsEarningRule->setMinOrderValue(100);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(608, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_points_earning_with_bellow_minimal(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);
        $pointsEarningRule->setMinOrderValue(300);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule]);

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(0, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_value_for_given_transaction_and_order_rules(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $pointsEarningRule2 = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule2->setPointValue(10);
        $pointsEarningRule2->setExcludeDeliveryCost(false);

        $multiplyPointsEarningRule = new MultiplyPointsForProductEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $multiplyPointsEarningRule->setMultiplier(3);
        $multiplyPointsEarningRule->setLabels([new Label('color', 'red')]);

        $multiplyPointsEarningRule2 = new MultiplyPointsForProductEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $multiplyPointsEarningRule2->setMultiplier(5);
        $multiplyPointsEarningRule2->setLabels([new Label('color', 'blue')]);

        $purchaseEarningRule = new ProductPurchaseEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $purchaseEarningRule->setSkuIds(['000']);
        $purchaseEarningRule->setPointsAmount(200);

        $evaluator = $this->getEarningRuleEvaluator(
            [
                $pointsEarningRule,
                $pointsEarningRule2,
                $multiplyPointsEarningRule,
                $multiplyPointsEarningRule2,
                $purchaseEarningRule,
            ]
        );

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(8264, $points);
    }

    /**
     * @test
     */
    public function it_returns_proper_comment_for_given_transaction(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(4);
        $pointsEarningRule->setExcludeDeliveryCost(false);
        $pointsEarningRule->setName('Test 1');
        $pointsEarningRule->setAllTimeActive(true);

        $pointsEarningRule1 = new MultiplyPointsForProductEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000001')
        );
        $pointsEarningRule1->setMultiplier(2);
        $pointsEarningRule1->setName('Test 2');
        $pointsEarningRule1->setSkuIds(['123', '000', '0001']);

        $evaluator = $this->getEarningRuleEvaluator([$pointsEarningRule, $pointsEarningRule1]);

        $pointsWithComment = $evaluator->evaluateTransactionWithComment(
            new TransactionId('00000000-0000-0000-0000-000000000000'),
            static::USER_ID
        );

        $this->assertArrayHasKey('points', $pointsWithComment);
        $this->assertArrayHasKey('comment', $pointsWithComment);
        $this->assertEquals(1216, $pointsWithComment['points']);
        $this->assertEquals('Test 1, Test 2', $pointsWithComment['comment']);
    }

    /**
     * @test
     */
    public function it_will_stop_on_last_earning_rule(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(1);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $finalEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $finalEarningRule->setPointValue(1);
        $finalEarningRule->setExcludeDeliveryCost(false);
        $finalEarningRule->setLastExecutedRule(true);

        $notExecutedEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $notExecutedEarningRule->setPointValue(10);
        $notExecutedEarningRule->setExcludeDeliveryCost(false);

        $evaluator = $this->getEarningRuleEvaluator(
            [$pointsEarningRule, $finalEarningRule, $notExecutedEarningRule]
        );

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(304, $points);
    }

    /**
     * @test
     */
    public function it_will_stops_only_on_executed_last_earning_rule(): void
    {
        $pointsEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $pointsEarningRule->setPointValue(1);
        $pointsEarningRule->setExcludeDeliveryCost(false);

        $finalNonExecutedEarningRule = new PointsEarningRule(new EarningRuleId('00000000-0000-0000-0000-000000000000'));
        $finalNonExecutedEarningRule->setPointValue(1);
        $finalNonExecutedEarningRule->setExcludeDeliveryCost(false);
        $finalNonExecutedEarningRule->setMinOrderValue(1000000);
        $finalNonExecutedEarningRule->setLastExecutedRule(true);

        $afterNonExecutedFinalEarningRule = new PointsEarningRule(
            new EarningRuleId('00000000-0000-0000-0000-000000000000')
        );
        $afterNonExecutedFinalEarningRule->setPointValue(2);
        $afterNonExecutedFinalEarningRule->setExcludeDeliveryCost(false);

        $evaluator = $this->getEarningRuleEvaluator(
            [$pointsEarningRule, $finalNonExecutedEarningRule, $afterNonExecutedFinalEarningRule]
        );

        $points = $evaluator->evaluateTransaction($this->getTransaction(), static::USER_ID);
        $this->assertEquals(456, $points);
    }

    /**
     * @param array $rules
     *
     * @return OloyEarningRuleEvaluator
     */
    protected function getEarningRuleEvaluator(array $rules): OloyEarningRuleEvaluator
    {
        return new OloyEarningRuleEvaluator(
            $this->getEarningRuleRepository($rules),
            $this->getEarningRuleAlgorithmFactory(),
            $this->getInvitationDetailsRepository(),
            $this->getSegmentedCustomersRepository(),
            $this->getCustomerDetailsRepository(),
            $this->getSettingsManager([Status::TYPE_ACTIVE]),
            $this->getStoppableProvider(),
            $this->getEarningGeoRuleRepository($rules),
            $this->getEarningQrcodeRuleRepository($rules),
            $this->getTransactionRepository()
        );
    }

    /**
     * @return EarningRuleAlgorithmFactoryInterface|MockObject
     */
    protected function getEarningRuleAlgorithmFactory(): MockObject
    {
        $algorithms = [
            PointsEarningRule::class => new PointsEarningRuleAlgorithm(),
            MultiplyPointsForProductEarningRule::class => new MultiplyPointsForProductRuleAlgorithm(),
            ProductPurchaseEarningRule::class => new ProductPurchaseEarningRuleAlgorithm(),
            MultiplyPointsByProductLabelsEarningRule::class => new MultiplyPointsByProductLabelsRuleAlgorithm(),
        ];

        $mock = $this->createMock(EarningRuleAlgorithmFactoryInterface::class);
        $mock->method('getAlgorithm')->will(
            $this->returnCallback(
                function ($class) use ($algorithms) {
                    return $algorithms[get_class($class)];
                }
            )
        );

        return $mock;
    }

    /**
     * @return MockObject|TransactionRepository
     */
    protected function getTransactionRepository(): MockObject
    {
        $transactionRepository = $this->getMockBuilder(TransactionRepository::class)->disableOriginalConstructor()->getMock();
        $transactionRepository->method('load')->willReturn($this->getTransaction());

        return $transactionRepository;
    }

    /**
     * @return Transaction
     */
    protected function getTransaction(): Transaction
    {
        return Transaction::createTransaction(
            new \OpenLoyalty\Component\Transaction\Domain\TransactionId('00000000-0000-0000-0000-000000000000'),
            [
                'purchaseDate' => new \DateTime(),
                'documentType' => Transaction::TYPE_SELL,
                'documentNumber' => '123',
                'purchasePlace' => 'Wroclaw',
            ],
            [
                'name' => 'John Doe',
                'address' => [],
            ],
            [
                new Item(
                    new SKU('123'),
                    'item1',
                    1,
                    12,
                    'cat',
                    $maker = 'test',
                    [
                        new Label('color', 'red'),
                    ]
                ),
                new Item(
                    new SKU('000'),
                    'item2',
                    1,
                    100,
                    'cat',
                    $maker = 'test',
                    [
                        new Label('color', 'blue'),
                    ]
                ),
                new Item(
                    new SKU('0001'),
                    'delivery',
                    1,
                    40,
                    'cat',
                    $maker = 'test'
                ),
            ],
            null,
            ['0001']
        );
    }

    /**
     * @return InvitationDetailsRepository|MockObject
     */
    protected function getInvitationDetailsRepository(): MockObject
    {
        $mock = $this->createMock(InvitationDetailsRepository::class);
        $mock->method('find')->with($this->isType('string'))
            ->willReturn([]);

        return $mock;
    }

    /**
     * @param array $earningGeoRules
     *
     * @return EarningRuleGeoRepository|MockObject
     */
    protected function getEarningGeoRuleRepository(array $earningGeoRules): MockObject
    {
        /** @var EarningRuleRepository|MockObject $mock */
        $mock = $this->createMock(EarningRuleGeoRepository::class);
        $mock->method('findGeoRules')
            ->willReturn($earningGeoRules);

        return $mock;
    }

    /**
     * @param array $earningQrcodeRules
     *
     * @return EarningRuleQrcodeRepository|MockObject
     */
    protected function getEarningQrcodeRuleRepository(array $earningQrcodeRules
    ): MockObject {
        /** @var EarningRuleRepository|MockObject $mock */
        $mock = $this->createMock(EarningRuleQrcodeRepository::class);
        $mock->method('findAllActiveQrcodeRules')->with(
            $this->isType('string'),
            $this->isType('string'),
            $this->isType('array'),
            $this->logicalOr(
                $this->isType('string'),
                $this->isNull()
            ),
            $this->logicalOr(
                $this->isInstanceOf(\DateTime::class),
                $this->isNull()
            )
        )->willReturn($earningQrcodeRules);

        return $mock;
    }

    /**
     * @param array $earningRules
     *
     * @return EarningRuleRepository|MockObject
     */
    protected function getEarningRuleRepository(array $earningRules): MockObject
    {
        /** @var EarningRuleRepository|MockObject $mock */
        $mock = $this->createMock(EarningRuleRepository::class);
        $mock->method('findAllActive')
            ->with(
                $this->logicalOr(
                    $this->isInstanceOf(\DateTime::class),
                    $this->isNull()
                )
            )
            ->willReturn(
                $earningRules
            );
        $mock->method('findAllActiveEventRules')->with(
            $this->isType('string'),
            $this->isType('array'),
            $this->logicalOr(
                $this->isType('string'),
                $this->isNull()
            ),
            $this->logicalOr(
                $this->isInstanceOf(\DateTime::class),
                $this->isNull()
            )
        )->willReturn($earningRules);

        $mock->method('findAllActiveEventRulesBySegmentsAndLevels')
            ->with(
                $this->logicalOr(
                    $this->isInstanceOf(\DateTime::class),
                    $this->isNull()
                ),
                $this->isType('array'),
                $this->logicalOr(
                    $this->isType('string'),
                    $this->isNull()
                )
            )
            ->willReturn($earningRules);

        return $mock;
    }

    /**
     * @return MockObject|SegmentedCustomersRepository
     */
    protected function getSegmentedCustomersRepository(): MockObject
    {
        $mock = $this->createMock(SegmentedCustomersRepository::class);

        $dataToReturn = [];

        $mock->method('findByParameters')
            ->with(
                $this->isType('array'),
                $this->isType('bool')
            )->willReturn($dataToReturn);

        return $mock;
    }

    /**
     * @return MockObject|CustomerDetailsRepository
     */
    protected function getCustomerDetailsRepository(): MockObject
    {
        $mock = $this->createMock(CustomerDetailsRepository::class);

        $dataToReturn = [];

        $mock->method('findOneByCriteria')
            ->with(
                $this->isType('array'),
                $this->isType('int')
            )->willReturn($dataToReturn);

        return $mock;
    }

    /**
     * @param array $statuses
     *
     * @return MockObject|SettingsManager
     */
    protected function getSettingsManager(array $statuses): MockObject
    {
        $settingsManager = $this->getMockBuilder(SettingsManager::class)->getMock();
        $settingsManager->method('getSettingByKey')->willReturn($statuses);

        return $settingsManager;
    }

    /**
     * @return StoppableProvider
     */
    protected function getStoppableProvider(): StoppableProvider
    {
        return new StoppableProvider();
    }
}
