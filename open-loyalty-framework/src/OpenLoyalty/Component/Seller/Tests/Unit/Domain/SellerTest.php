<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Seller\Tests\Unit\Domain;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasActivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasDeactivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasDeleted;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasRegistered;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasUpdated;
use OpenLoyalty\Component\Seller\Domain\Seller;
use OpenLoyalty\Component\Seller\Domain\SellerId;

/**
 * Class SellerTest.
 */
final class SellerTest extends AggregateRootScenarioTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getAggregateRootClass(): string
    {
        return Seller::class;
    }

    /**
     * @test
     */
    public function it_can_register_seller(): void
    {
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000000');
        $sellerData = [];

        $this->scenario->when(function () use ($sellerId, $sellerData) {
            return Seller::registerSeller($sellerId, $sellerData);
        })->then([
            new SellerWasRegistered(
                $sellerId,
                $sellerData
            ),
        ]);
    }

    /**
     * @test
     */
    public function it_can_update_seller_data(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $sellerId = new SellerId($id);
        $sellerData = [];

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new SellerWasRegistered($sellerId, $sellerData),
            ])
            ->when(function (Seller $seller) use ($sellerData) {
                $seller->update($sellerData);
            })->then([
                new SellerWasUpdated(
                    $sellerId,
                    $sellerData
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_activate_seller(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $sellerId = new SellerId($id);
        $sellerData = [];

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new SellerWasRegistered($sellerId, $sellerData),
            ])
            ->when(function (Seller $seller) {
                $seller->activate();
            })->then([
                new SellerWasActivated(
                    $sellerId
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_deactivate_seller(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $sellerId = new SellerId($id);
        $sellerData = [];

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new SellerWasRegistered($sellerId, $sellerData),
            ])
            ->when(function (Seller $seller) {
                $seller->deactivate();
            })->then([
                new SellerWasDeactivated(
                    $sellerId
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_delete_seller(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $sellerId = new SellerId($id);
        $sellerData = [];

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new SellerWasRegistered($sellerId, $sellerData),
            ])
            ->when(function (Seller $seller) {
                $seller->delete();
            })->then([
                new SellerWasDeleted(
                    $sellerId
                ),
            ]);
    }
}
