<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use Webmozart\Assert\Assert;

/**
 * Class PhotoId.
 */
class PhotoId
{
    /**
     * @var string
     */
    private $id;

    /**
     * PhotoId constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        Assert::uuid($id);

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->id;
    }
}
