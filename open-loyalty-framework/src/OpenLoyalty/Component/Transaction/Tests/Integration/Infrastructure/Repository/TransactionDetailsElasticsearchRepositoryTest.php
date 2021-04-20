<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Tests\Integration\Infrastructure\Repository;

use OpenLoyalty\Bundle\TransactionBundle\DataFixtures\ORM\LoadNotMatchedTransactionData;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Infrastructure\Repository\TransactionDetailsElasticsearchRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TransactionDetailsElasticsearchRepositoryTest.
 */
class TransactionDetailsElasticsearchRepositoryTest extends KernelTestCase
{
    /**
     * @var TransactionDetailsElasticsearchRepository
     */
    private $transactionDetailsRepository;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        static::bootKernel();

        $container = self::$kernel->getContainer();
        $this->transactionDetailsRepository = $container->get(TransactionDetailsElasticsearchRepository::class);
    }

    /**
     * @test
     * @dataProvider getTransactionsDocumentNumberDataProvider
     *
     * @param string $documentNumber
     */
    public function it_returns_transaction_by_document_number(string $documentNumber)
    {
        $transaction = $this->transactionDetailsRepository->findTransactionByDocumentNumber($documentNumber);
        $this->assertInstanceOf(TransactionDetails::class, $transaction);
    }

    /**
     * @test
     * @dataProvider getTransactionsDocumentNumberDataProvider
     *
     * @param string $revisedDocumentNumber
     */
    public function it_returns_transaction_by_revised_document_number(string $revisedDocumentNumber)
    {
        $transactions = $this->transactionDetailsRepository->findReturnsByDocumentNumber($revisedDocumentNumber, false);
        $this->assertEquals(1, count($transactions));
    }

    /**
     * @return array
     */
    public function getTransactionsDocumentNumberDataProvider(): array
    {
        $data = [];
        $fixtures = new LoadNotMatchedTransactionData();

        foreach ($fixtures->getTransactionsDocumentNumber() as $documentNumber) {
            $data[] = [$documentNumber];
        }

        return $data;
    }
}
