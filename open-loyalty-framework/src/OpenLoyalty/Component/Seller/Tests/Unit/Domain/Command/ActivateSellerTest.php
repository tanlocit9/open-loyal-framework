<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Seller\Domain\Command\ActivateSeller;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasActivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasRegistered;
use OpenLoyalty\Component\Seller\Domain\PosId;
use OpenLoyalty\Component\Seller\Domain\SellerId;

/**
 * Class ActivateSellerTest.
 */
class ActivateSellerTest extends SellerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_activates_seller()
    {
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000000');
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'open@loyalty.com',
            'phone' => '123456789',
            'posId' => new PosId('00000000-0000-0000-0000-000000000000'),
            'createdAt' => new \DateTime(),
        ];
        $this->scenario
            ->withAggregateId($sellerId)
            ->given([new SellerWasRegistered($sellerId, $data)])
            ->when(new ActivateSeller($sellerId))
            ->then(array(
                new SellerWasActivated($sellerId),
            ));
    }
}
