<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Tests\Unit\Domain\Validator;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetails;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use OpenLoyalty\Component\Seller\Domain\Validator\SellerUniqueValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class SellerUniqueValidatorTest.
 */
class SellerUniqueValidatorTest extends TestCase
{
    /**
     * @var Repository
     */
    protected $sellerDetailsRepository;

    public function setUp()
    {
        $seller1 = new SellerDetails(new SellerId('00000000-0000-0000-0000-000000000011'));
        $seller1->setEmail('a@a.com');
        $seller2 = new SellerDetails(new SellerId('00000000-0000-0000-0000-000000000012'));
        $seller2->setEmail('b@b.com');
        $seller3 = new SellerDetails(new SellerId('00000000-0000-0000-0000-000000000012'));
        $seller3->setEmail('c@c.com');
        $sellers = [
            'a@a.com' => $seller1,
            'b@b.com' => $seller2,
            'c@c.com' => $seller3,
        ];

        $this->sellerDetailsRepository = $this->getMockBuilder('Broadway\ReadModel\Repository')->getMock();
        $this->sellerDetailsRepository->method('findBy')->with(
            $this->arrayHasKey('email')
        )
            ->will($this->returnCallback(function ($params) use ($sellers) {
                if (isset($params['email'])) {
                    $email = $params['email'];

                    return array_filter($sellers, function (SellerDetails $sellerDetails) use ($email) {
                        if ($sellerDetails->getEmail() == $email) {
                            return true;
                        }

                        return false;
                    });
                }

                return [];
            }));
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Seller\Domain\Exception\EmailAlreadyExistsException
     */
    public function it_throws_exception_when_email_is_not_unique()
    {
        $validator = new SellerUniqueValidator($this->sellerDetailsRepository);
        $validator->validateEmailUnique('a@a.com');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_email_belongs_to_user()
    {
        $validator = new SellerUniqueValidator($this->sellerDetailsRepository);
        $validator->validateEmailUnique('a@a.com', new SellerId('00000000-0000-0000-0000-000000000011'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_email_is_unique()
    {
        $validator = new SellerUniqueValidator($this->sellerDetailsRepository);
        $validator->validateEmailUnique('a2@a.com');
    }
}
