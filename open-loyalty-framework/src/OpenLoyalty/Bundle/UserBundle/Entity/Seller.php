<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenLoyalty\Component\Seller\Domain\SellerId;

/**
 * Class Seller.
 *
 * @ORM\Entity()
 */
class Seller extends User
{
    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, name="allow_point_transfer", options={"default":false})
     */
    protected $allowPointTransfer = false;

    /**
     * Seller constructor.
     *
     * @param SellerId $id
     */
    public function __construct(SellerId $id)
    {
        parent::__construct((string) $id);
    }

    /**
     * @return bool
     */
    public function isAllowPointTransfer(): bool
    {
        return $this->allowPointTransfer;
    }

    /**
     * @param bool $allowPointTransfer
     */
    public function setAllowPointTransfer(bool $allowPointTransfer): void
    {
        $this->allowPointTransfer = $allowPointTransfer;
    }
}
