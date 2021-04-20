<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Model;

/**
 * Class Coupon.
 */
class Coupon
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var null|string
     */
    protected $id = null;

    /**
     * Coupon constructor.
     *
     * @param string      $code
     * @param string|null $id
     */
    public function __construct(string $code, ?string $id = null)
    {
        $this->code = $code;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}
