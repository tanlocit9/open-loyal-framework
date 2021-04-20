<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

/**
 * Class Coupon.
 */
class Coupon
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $code;

    /**
     * Coupon constructor.
     *
     * @param string $id
     * @param string $code
     */
    public function __construct(string $id, string $code)
    {
        $this->id = $id;
        $this->code = $code;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
