<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain;

use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\Model\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerTest.
 */
final class CustomerTest extends TestCase
{
    /**
     * @test
     * @dataProvider getTransactions
     *
     * @param array $transactions
     * @param int   $expectedAmount
     * @param int   $expectedCount
     */
    public function it_returns_correct_transactions_amount(array $transactions, int $expectedAmount, int $expectedCount): void
    {
        /** @var Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)
                         ->setMethods(['getTransactions'])
                         ->disableOriginalConstructor()
                         ->getMock();

        $customer->expects($this->any())->method('getTransactions')->willReturn($transactions);

        $result = $customer->getTransactionsAmount();
        $this->assertEquals($expectedAmount, $result);
    }

    /**
     * @test
     * @dataProvider getTransactions
     *
     * @param array $transactions
     * @param int   $expectedAmount
     * @param int   $expectedCount
     */
    public function it_returns_correct_transactions_count(array $transactions, int $expectedAmount, int $expectedCount): void
    {
        /** @var Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)
                         ->setMethods(['getTransactions'])
                         ->disableOriginalConstructor()
                         ->getMock();

        $customer->expects($this->any())->method('getTransactions')->willReturn($transactions);

        $result = $customer->getTransactionsCount();
        $this->assertEquals($expectedCount, $result);
    }

    /**
     * @return array
     */
    public function getTransactions(): array
    {
        $sellTransaction = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $sellTransaction->method('isReturn')->willReturn(false);
        $sellTransaction->method('getDocumentNumber')->willReturn('123');
        $sellTransaction->method('getRevisedDocument')->willReturn('');
        $sellTransaction->method('getGrossValue')->willReturn(9);

        $sellTransaction1 = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $sellTransaction1->method('isReturn')->willReturn(false);
        $sellTransaction1->method('getDocumentNumber')->willReturn('234');
        $sellTransaction1->method('getRevisedDocument')->willReturn('');
        $sellTransaction1->method('getGrossValue')->willReturn(9);

        $sellTransaction2 = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $sellTransaction2->method('isReturn')->willReturn(false);
        $sellTransaction2->method('getDocumentNumber')->willReturn('345');
        $sellTransaction2->method('getRevisedDocument')->willReturn('');
        $sellTransaction2->method('getGrossValue')->willReturn(9);

        $returnTransaction = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $returnTransaction->method('isReturn')->willReturn(true);
        $returnTransaction->method('getRevisedDocument')->willReturn('123');
        $returnTransaction->method('getDocumentNumber')->willReturn('1234');
        $returnTransaction->method('getGrossValue')->willReturn(-9);

        $returnTransaction1 = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $returnTransaction1->method('isReturn')->willReturn(true);
        $returnTransaction1->method('getRevisedDocument')->willReturn('234');
        $returnTransaction1->method('getDocumentNumber')->willReturn('2345');
        $returnTransaction1->method('getGrossValue')->willReturn(-3);

        $returnTransaction1_2 = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $returnTransaction1_2->method('isReturn')->willReturn(true);
        $returnTransaction1_2->method('getRevisedDocument')->willReturn('234');
        $returnTransaction1_2->method('getDocumentNumber')->willReturn('2345_2');
        $returnTransaction1_2->method('getGrossValue')->willReturn(-6);

        $returnTransaction1_3 = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $returnTransaction1_3->method('isReturn')->willReturn(true);
        $returnTransaction1_3->method('getRevisedDocument')->willReturn('234');
        $returnTransaction1_3->method('getDocumentNumber')->willReturn('2345_3');
        $returnTransaction1_3->method('getGrossValue')->willReturn(-6);

        $returnTransaction2 = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $returnTransaction2->method('isReturn')->willReturn(true);
        $returnTransaction2->method('getRevisedDocument')->willReturn('345');
        $returnTransaction2->method('getDocumentNumber')->willReturn('3456');
        $returnTransaction2->method('getGrossValue')->willReturn(-9);

        return [
            [[$sellTransaction, $returnTransaction, $sellTransaction1, $returnTransaction1, $sellTransaction2, $returnTransaction2], 6, 1],
            [[$sellTransaction1, $returnTransaction1, $returnTransaction1_2], 0, 0],
            [[$sellTransaction1, $returnTransaction1, $returnTransaction1_2, $returnTransaction1_3], 0, 0],
        ];
    }
}
