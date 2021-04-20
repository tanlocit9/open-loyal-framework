<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\SellerVoter;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetails;
use OpenLoyalty\Component\Seller\Domain\SellerId;

/**
 * Class SellerVoterTest.
 */
class SellerVoterTest extends BaseVoterTest
{
    const SELLER_ID = '00000000-0000-474c-b092-b0dd880c0700';
    const SELLER2_ID = '00000000-0000-474c-b092-b0dd880c0701';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            SellerVoter::CREATE_SELLER => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            SellerVoter::LIST_SELLERS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            SellerVoter::VIEW => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::SELLER_ID],
            SellerVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::SELLER_ID],
            SellerVoter::ACTIVATE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::SELLER_ID],
            SellerVoter::DELETE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::SELLER_ID],
            SellerVoter::DEACTIVATE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::SELLER_ID],
            SellerVoter::ASSIGN_POS_TO_SELLER => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
        ];

        $voter = new SellerVoter();

        $this->assertVoterAttributes($voter, $attributes);

        $attributes = [
            SellerVoter::VIEW => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::USER_ID],
        ];

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        $seller = $this->getMockBuilder(SellerDetails::class)->disableOriginalConstructor()->getMock();
        $seller->method('getSellerId')->willReturn(new SellerId($id));

        return $seller;
    }
}
