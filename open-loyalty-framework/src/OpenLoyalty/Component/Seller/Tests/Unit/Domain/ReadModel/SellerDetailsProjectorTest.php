<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Tests\Unit\Domain\ReadModel;

use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Projector;
use Broadway\ReadModel\Testing\ProjectorScenarioTestCase;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasActivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasDeactivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasDeleted;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasRegistered;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasUpdated;
use OpenLoyalty\Component\Seller\Domain\PosId;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetails;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsProjector;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class SellerDetailsProjectorTest.
 */
class SellerDetailsProjectorTest extends ProjectorScenarioTestCase
{
    /**
     * @param InMemoryRepository $repository
     *
     * @return Projector
     */
    protected function createProjector(InMemoryRepository $repository): Projector
    {
        /** @var PosRepository|MockObject $posRepo */
        $posRepo = $this->getMockBuilder(PosRepository::class)->getMock();
        $posRepo->method('findBy')->willReturn(null);

        return new SellerDetailsProjector($repository, $posRepo);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_register(): void
    {
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000000');
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'open@loyalty.com',
            'phone' => '123456789',
            'posId' => (new PosId('00000000-0000-0000-0000-000000000000'))->__toString(),
            'createdAt' => new \DateTime(),
            'sellerId' => $sellerId->__toString(),
            'allowPointTransfer' => true,
        ];

        $expectedReadModel = SellerDetails::deserialize($data);
        $this->scenario->given([])
            ->when(new SellerWasRegistered($sellerId, $data))
            ->then(
                [
                    $expectedReadModel,
                ]
            );
    }

    /**
     * @test
     */
    public function it_activates_seller(): void
    {
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000000');
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'open@loyalty.com',
            'phone' => '123456789',
            'posId' => (new PosId('00000000-0000-0000-0000-000000000000'))->__toString(),
            'createdAt' => new \DateTime(),
            'sellerId' => $sellerId->__toString(),
        ];

        /** @var SellerDetails $expectedReadModel */
        $expectedReadModel = SellerDetails::deserialize($data);
        $expectedReadModel->setActive(true);
        $this->scenario
            ->given(
                [
                    new SellerWasRegistered($sellerId, $data),
                ]
            )
            ->when(new SellerWasActivated($sellerId))
            ->then(
                [
                    $expectedReadModel,
                ]
            );
    }

    /**
     * @test
     */
    public function it_deactivates_seller(): void
    {
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000000');
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'open@loyalty.com',
            'phone' => '123456789',
            'posId' => (new PosId('00000000-0000-0000-0000-000000000000'))->__toString(),
            'createdAt' => new \DateTime(),
            'sellerId' => $sellerId->__toString(),
        ];

        /** @var SellerDetails $expectedReadModel */
        $expectedReadModel = SellerDetails::deserialize($data);
        $expectedReadModel->setActive(false);
        $this->scenario
            ->given(
                [
                    new SellerWasRegistered($sellerId, $data),
                    new SellerWasActivated($sellerId),
                ]
            )
            ->when(new SellerWasDeactivated($sellerId))
            ->then(
                [
                    $expectedReadModel,
                ]
            );
    }

    /**
     * @test
     */
    public function it_deletes_seller(): void
    {
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000000');
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'open@loyalty.com',
            'phone' => '123456789',
            'posId' => (new PosId('00000000-0000-0000-0000-000000000000'))->__toString(),
            'createdAt' => new \DateTime(),
            'sellerId' => $sellerId->__toString(),
        ];

        /** @var SellerDetails $expectedReadModel */
        $expectedReadModel = SellerDetails::deserialize($data);
        $expectedReadModel->setDeleted(true);
        $this->scenario
            ->given(
                [
                    new SellerWasRegistered($sellerId, $data),
                ]
            )
            ->when(new SellerWasDeleted($sellerId))
            ->then(
                [
                    $expectedReadModel,
                ]
            );
    }

    /**
     * @test
     */
    public function it_updates_seller(): void
    {
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000000');
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'open@loyalty.com',
            'phone' => '123456789',
            'posId' => (new PosId('00000000-0000-0000-0000-000000000000'))->__toString(),
            'createdAt' => new \DateTime(),
            'sellerId' => $sellerId->__toString(),
            'allowPointTransfer' => true,
        ];

        /** @var SellerDetails $expectedReadModel */
        $expectedReadModel = SellerDetails::deserialize($data);
        $expectedReadModel->setLastName('Kowalski');
        $this->scenario
            ->given(
                [
                    new SellerWasRegistered($sellerId, $data),
                ]
            )
            ->when(new SellerWasUpdated($sellerId, ['lastName' => 'Kowalski']))
            ->then(
                [
                    $expectedReadModel,
                ]
            );
    }
}
