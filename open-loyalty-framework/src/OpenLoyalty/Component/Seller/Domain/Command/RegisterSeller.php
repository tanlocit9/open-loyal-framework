<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Domain\Command;

use OpenLoyalty\Component\Seller\Domain\SellerId;
use Assert\Assertion as Assert;

/**
 * Class RegisterSeller.
 */
class RegisterSeller extends SellerCommand
{
    protected $sellerData;

    /**
     * RegisterCustomerCommand.
     *
     * @param SellerId $sellerId
     * @param $sellerData
     */
    public function __construct(SellerId $sellerId, $sellerData)
    {
        $this->validate($sellerData);

        parent::__construct($sellerId);

        if (!isset($sellerData['createdAt'])) {
            $sellerData['createdAt'] = (new \DateTime())->getTimestamp();
        }
        $this->sellerData = $sellerData;
    }

    /**
     * @return mixed
     */
    public function getSellerData()
    {
        return $this->sellerData;
    }

    protected function validate(array $data)
    {
        Assert::keyIsset($data, 'firstName');
        Assert::keyIsset($data, 'lastName');
        Assert::keyIsset($data, 'email');
        Assert::keyIsset($data, 'posId');
        Assert::notBlank($data, 'firstName');
        Assert::notBlank($data, 'lastName');
        Assert::notBlank($data, 'email');
        Assert::notBlank($data, 'posId');
    }
}
