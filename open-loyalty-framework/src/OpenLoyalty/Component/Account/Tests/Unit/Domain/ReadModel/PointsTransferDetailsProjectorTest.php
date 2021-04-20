<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Domain\ReadModel;

use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Projector;
use Broadway\Repository\Repository as AggregateRootRepository;
use Broadway\ReadModel\Testing\ProjectorScenarioTestCase;
use OpenLoyalty\Component\Account\Domain\Account;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenCanceled;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenExpired;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenUnlocked;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereAdded;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereSpent;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereTransferred;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PAddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PSpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsProjector;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PointsTransferDetailsProjectorTest.
 */
final class PointsTransferDetailsProjectorTest extends ProjectorScenarioTestCase
{
    /**
     * @var AccountId
     */
    protected $accountId;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * {@inheritdoc}
     */
    protected function createProjector(InMemoryRepository $repository): Projector
    {
        $this->accountId = new AccountId('00000000-0000-0000-0000-000000000000');
        $this->customerId = new CustomerId('00000000-1111-0000-0000-000000000000');

        /** @var AggregateRootRepository|MockObject $accountRepository */
        $accountRepository = $this->getMockBuilder(AggregateRootRepository::class)->getMock();
        $account = Account::createAccount($this->accountId, $this->customerId);
        $accountRepository->method('load')->willReturn($account);

        /** @var AggregateRootRepository|MockObject $customerRepository */
        $customerRepository = $this->getMockBuilder(AggregateRootRepository::class)->getMock();
        $customer = Customer::registerCustomer(
            new \OpenLoyalty\Component\Customer\Domain\CustomerId($this->customerId->__toString()),
            $this->getCustomerData()
        );
        $customerRepository->method('load')->willReturn($customer);

        /** @var AggregateRootRepository|MockObject $transactionRepo */
        $transactionRepo = $this->getMockBuilder(AggregateRootRepository::class)->getMock();
        $transactionRepo->method('load')->willReturn(null);

        /** @var PosRepository|MockObject $posRepo */
        $posRepo = $this->getMockBuilder(PosRepository::class)->getMock();

        return new PointsTransferDetailsProjector($repository, $accountRepository, $customerRepository, $transactionRepo, $posRepo);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_add_points_transfer(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('pending');
        $expectedReadModel->setType('adding');

        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $lockedUntil = clone $date;
        $lockedUntil->modify('+2 days');
        $expiresAtDate = clone $lockedUntil;
        $expiresAtDate->setTime(23, 59, 59);
        $expectedReadModel->setLockedUntil($lockedUntil);
        $expiresAtDate->modify(sprintf('+%u days', 10));
        $expectedReadModel->setExpiresAt($expiresAtDate);
        $this->scenario->given([])
            ->when(new PointsWereAdded($this->accountId, new AddPointsTransfer($pointsId, 100, 10, 2, $date)))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_p2p_add_points_transfer(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $accountId = new AccountId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('pending');
        $expectedReadModel->setType('p2p_adding');
        $expectedReadModel->setSenderId($this->customerId);

        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $lockedUntil = clone $date;
        $lockedUntil->modify('+2 days');
        $expiresAtDate = clone $lockedUntil;
        $expiresAtDate->setTime(23, 59, 59);
        $expectedReadModel->setLockedUntil($lockedUntil);
        $expiresAtDate->modify(sprintf('+%u days', 10));
        $expectedReadModel->setExpiresAt($expiresAtDate);
        $this->scenario->given([])
            ->when(new PointsWereAdded($this->accountId, new P2PAddPointsTransfer($accountId, $pointsId, 100, 10, 2, $date)))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_add_points_transfer_without_lock(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('active');
        $expectedReadModel->setType('adding');

        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $expiresAtDate = clone $date;
        $expiresAtDate->setTime(23, 59, 59);
        $lockedUntil = null;
        $expectedReadModel->setLockedUntil($lockedUntil);
        $expiresAtDate->modify(sprintf('+%u days', 10));
        $expectedReadModel->setExpiresAt($expiresAtDate);
        $this->scenario->given([])
            ->when(new PointsWereAdded($this->accountId, new AddPointsTransfer($pointsId, 100, 10, null, $date)))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_add_points_transfer_with_lock(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('pending');
        $expectedReadModel->setType('adding');

        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $lockedUntil = clone $date;
        $lockedUntil->modify(sprintf('+%u days', 10));
        $expiresAtDate = clone $lockedUntil;
        $expiresAtDate->setTime(23, 59, 59);
        $expectedReadModel->setLockedUntil($lockedUntil);
        $expiresAtDate->modify(sprintf('+%u days', 10));
        $expectedReadModel->setExpiresAt($expiresAtDate);
        $this->scenario->given([])
            ->when(new PointsWereAdded($this->accountId, new AddPointsTransfer($pointsId, 100, 10, 10, $date)))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_spending_points_transfer(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('active');
        $expectedReadModel->setType('spending');

        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $expiresAtDate = clone $date;
        $expiresAtDate->modify(sprintf('+%u days', 0));
        $expectedReadModel->setExpiresAt($expiresAtDate);
        $this->scenario->given([])
            ->when(new PointsWereSpent($this->accountId, new SpendPointsTransfer($pointsId, 100, $date)))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_p2p_spending_points_transfer(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $accountId = new AccountId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('active');
        $expectedReadModel->setType('p2p_spending');
        $expectedReadModel->setReceiverId($this->customerId);

        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $expiresAtDate = clone $date;
        $expiresAtDate->modify(sprintf('+%u days', 0));
        $expectedReadModel->setExpiresAt($expiresAtDate);
        $this->scenario->given([])
            ->when(new PointsWereTransferred($this->accountId, new P2PSpendPointsTransfer($accountId, $pointsId, 100, $date)))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_cancels_previously_added_transfer(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('canceled');
        $expectedReadModel->setType('adding');
        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $expiresAtDate = clone $date;
        $expiresAtDate->setTime(23, 59, 59);
        $expiresAtDate->modify(sprintf('+%u days', 10));
        $expectedReadModel->setExpiresAt($expiresAtDate);
        $this->scenario
            ->given([
                new PointsWereAdded($this->accountId, new AddPointsTransfer($pointsId, 100, 10, null, $date)),
            ])
            ->when(new PointsTransferHasBeenCanceled($this->accountId, $pointsId))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_expires_previously_added_transfer(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('expired');
        $expectedReadModel->setType('adding');
        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $expiresAtDate = clone $date;
        $expiresAtDate->setTime(23, 59, 59);
        $expiresAtDate->modify(sprintf('+%u days', 10));
        $expectedReadModel->setExpiresAt($expiresAtDate);
        $this->scenario
            ->given([
                new PointsWereAdded($this->accountId, new AddPointsTransfer($pointsId, 100, 10, null, $date)),
            ])
            ->when(new PointsTransferHasBeenExpired($this->accountId, $pointsId))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_unlock_previously_added_transfer(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $expectedReadModel = $this->createReadModel($pointsId);
        $expectedReadModel->setValue(100.0);
        $expectedReadModel->setState('active');
        $expectedReadModel->setType('adding');
        $date = new \DateTime();
        $expectedReadModel->setCreatedAt($date);
        $lockedUntil = clone $date;
        $lockedUntil->modify(sprintf('+%u days', 10));
        $expiresAtDate = clone $lockedUntil;
        $expiresAtDate->setTime(23, 59, 59);
        $expectedReadModel->setLockedUntil($lockedUntil);
        $expiresAtDate->modify(sprintf('+%u days', 10));
        $expectedReadModel->setExpiresAt($expiresAtDate);

        $this->scenario
            ->given([
                new PointsWereAdded($this->accountId, new AddPointsTransfer($pointsId, 100, 10, 10, $date)),
            ])
            ->when(new PointsTransferHasBeenUnlocked($this->accountId, $pointsId))
            ->then([$expectedReadModel]);
    }

    /**
     * @param PointsTransferId $pointsTransferId
     *
     * @return PointsTransferDetails
     */
    private function createReadModel(PointsTransferId $pointsTransferId): PointsTransferDetails
    {
        $model = new PointsTransferDetails($pointsTransferId, $this->customerId, $this->accountId);
        $customerData = $this->getCustomerData();
        $model->setCustomerFirstName($customerData['firstName']);
        $model->setCustomerLastName($customerData['lastName']);
        $model->setCustomerPhone($customerData['phone']);
        $model->setCustomerEmail($customerData['email']);

        return $model;
    }

    /**
     * @return array
     */
    private function getCustomerData(): array
    {
        return [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'gender' => 'male',
            'email' => 'customer@open.com',
            'birthDate' => 653011200,
            'phone' => '+48123123123',
            'createdAt' => 1470646394,
            'loyaltyCardNumber' => '000000',
            'company' => [
                'name' => 'test',
                'nip' => 'nip',
            ],
            'address' => [
                'street' => 'Dmowskiego',
                'address1' => '21',
                'city' => 'Wrocław',
                'country' => 'PL',
                'postal' => '50-300',
                'province' => 'Dolnośląskie',
            ],
        ];
    }
}
