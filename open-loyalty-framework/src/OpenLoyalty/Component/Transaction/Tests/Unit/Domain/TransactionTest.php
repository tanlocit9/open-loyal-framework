<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Transaction\Tests\Unit\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Core\Domain\Model\SKU;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use PHPUnit\Framework\TestCase;

/**
 * Class TransactionDetailsTest.
 */
class TransactionTest extends TestCase
{
    /**
     * @test
     */
    public function it_correctly_calculates_values(): void
    {
        $transaction = $this->getTransactionDetails();
        $this->assertEquals(110, $transaction->getGrossValue());
        $this->assertEquals(110, $transaction->getGrossValueWithoutDeliveryCosts());
    }

    /**
     * @test
     */
    public function it_correctly_calculates_values_and_take_delivery_costs_into_account(): void
    {
        $transaction = $this->getTransactionDetails(['123']);
        $this->assertEquals(110, $transaction->getGrossValue());
        $this->assertEquals(100, $transaction->getGrossValueWithoutDeliveryCosts());
    }

    /**
     * @test
     */
    public function it_correctly_calculates_values_and_take_additional_excluded_skus_into_account(): void
    {
        $transaction = $this->getTransactionDetails(['345']);
        $this->assertEquals(100, $transaction->getGrossValue([new SKU('123')]));
        $this->assertEquals(100, $transaction->getGrossValue([new SKU('123')]));
        $this->assertEquals(0, $transaction->getGrossValueWithoutDeliveryCosts([new SKU('123')]));
        $this->assertEquals(0, $transaction->getGrossValueWithoutDeliveryCosts([new SKU('123')]));
    }

    /**
     * @test
     */
    public function it_correctly_calculates_values_and_take_additional_excluded_labels_into_account(): void
    {
        $transaction = $this->getTransactionDetails(['345']);
        $this->assertEquals(100, $transaction->getGrossValue([], [new Label('1', '1')]));
        $this->assertEquals(0, $transaction->getGrossValueWithoutDeliveryCosts([], [new Label('1', '1')]));
    }

    /**
     * @test
     */
    public function it_correctly_calculates_values_and_take_additional_included_labels_into_account(): void
    {
        $transaction = $this->getTransactionDetails(['345']);
        $this->assertEquals(100, $transaction->getGrossValue([], [], [new Label('2', '2')]));
        $this->assertEquals(0, $transaction->getGrossValueWithoutDeliveryCosts([], [], [new Label('2', '2')]));
    }

    /**
     * @param array|null $excludedDeliverySKUs
     *
     * @return Transaction
     */
    protected function getTransactionDetails(array $excludedDeliverySKUs = null): Transaction
    {
        $transactionId = new TransactionId('00000000-0000-0000-0000-000000000000');
        $item1 = new Item(new SKU('123'), 'test', 1, 10, 'test', 'test', [
            new Label('1', '1'),
        ]);
        $item2 = new Item(new SKU('345'), 'test', 1, 100, 'test', 'test', [
            new Label('2', '2'),
        ]);

        $transaction = Transaction::createTransaction(
            $transactionId,
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
                $item1,
                $item2,
            ],
            null,
            $excludedDeliverySKUs
        );

        return $transaction;
    }
}
