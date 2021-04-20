<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Domain\Command;

use OpenLoyalty\Component\Seller\Domain\SellerId;

/**
 * Class UpdateSeller.
 */
class UpdateSeller extends SellerCommand
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
        parent::__construct($sellerId);

        $this->sellerData = $sellerData;
    }

    /**
     * @return mixed
     */
    public function getSellerData()
    {
        return $this->sellerData;
    }
}
