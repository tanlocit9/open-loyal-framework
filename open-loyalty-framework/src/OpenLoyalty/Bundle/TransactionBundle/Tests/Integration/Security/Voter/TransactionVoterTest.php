<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Tests\Integration\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\TransactionBundle\Security\Voter\TransactionVoter;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class TransactionVoterTest.
 */
class TransactionVoterTest extends BaseVoterTest
{
    const TRANSACTION_ID = '00000000-0000-474c-b092-b0dd880c0700';
    const TRANSACTION2_ID = '00000000-0000-474c-b092-b0dd880c0701';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            TransactionVoter::LIST_TRANSACTIONS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            TransactionVoter::LIST_CURRENT_CUSTOMER_TRANSACTIONS => ['seller' => false, 'customer' => true, 'admin' => false, 'admin_reporter' => false],
            TransactionVoter::LIST_CURRENT_POS_TRANSACTIONS => ['seller' => true, 'customer' => false, 'admin' => false, 'admin_reporter' => false],
            TransactionVoter::VIEW => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::TRANSACTION_ID],
            TransactionVoter::EDIT_TRANSACTION_LABELS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            TransactionVoter::CREATE_TRANSACTION => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            TransactionVoter::ASSIGN_CUSTOMER_TO_TRANSACTION => ['seller' => true, 'customer' => true, 'admin' => true, 'admin_reporter' => false, 'subject' => $this->getTransactionMock(self::TRANSACTION_ID)],
            TransactionVoter::APPEND_LABELS_TO_TRANSACTION => ['seller' => false, 'customer' => true, 'admin' => false, 'admin_reporter' => false, 'id' => self::TRANSACTION2_ID],
            TransactionVoter::LIST_ITEM_LABELS => ['seller' => true, 'customer' => true, 'admin' => true, 'admin_reporter' => true],
            TransactionVoter::LIST_CUSTOMER_TRANSACTIONS => ['seller' => false, 'customer' => true, 'admin' => true, 'admin_reporter' => true, 'subject' => $this->getCustomerDetailsMock()],
        ];

        /** @var SellerDetailsRepository|MockObject $sellerDetailsRepositoryMock */
        $sellerDetailsRepositoryMock = $this->getMockBuilder(SellerDetailsRepository::class)->getMock();
        $sellerDetailsRepositoryMock
            ->method('find')
            ->with($this->isType('string'))
            ->willReturn(null)
        ;

        $voter = new TransactionVoter($sellerDetailsRepositoryMock);

        $this->assertVoterAttributes($voter, $attributes);

        $attributes = [
            TransactionVoter::VIEW => ['seller' => true, 'customer' => true, 'admin' => true, 'id' => self::TRANSACTION2_ID],
        ];

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     *
     * @return MockObject|TransactionDetails
     */
    protected function getSubjectById($id)
    {
        $transaction = $this->getMockBuilder(TransactionDetails::class)->disableOriginalConstructor()->getMock();
        $transaction->method('getTransactionId')->willReturn(new TransactionId($id));
        $customerId = null;
        if ($id == self::TRANSACTION2_ID) {
            $customerId = new CustomerId(self::USER_ID);
        }
        $transaction->method('getCustomerId')->willReturn($customerId);

        return $transaction;
    }

    /**
     * @param string $id
     *
     * @return MockObject
     */
    protected function getTransactionMock(string $id): MockObject
    {
        $transaction = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $transaction->method('getTransactionId')->willReturn(new TransactionId($id));
        $customerId = null;
        if ($id == self::TRANSACTION2_ID) {
            $customerId = new CustomerId(self::USER_ID);
        }
        $transaction->method('getCustomerId')->willReturn($customerId);

        return $transaction;
    }

    /**
     * @return MockObject|CustomerDetails
     */
    protected function getCustomerDetailsMock(): MockObject
    {
        return $this->getMockBuilder(CustomerDetails::class)->disableOriginalConstructor()->getMock();
    }
}
