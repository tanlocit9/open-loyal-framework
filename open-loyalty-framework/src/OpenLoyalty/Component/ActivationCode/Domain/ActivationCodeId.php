<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\ActivationCode\Domain;

use Assert\Assertion as Assert;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;

/**
 * Class ActivationCodeId.
 */
class ActivationCodeId implements Identifier
{
    /**
     * @var string
     */
    private $id;

    /**
     * ActivationCodeId constructor.
     *
     * @param $id
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct($id)
    {
        Assert::string($id);
        Assert::uuid($id);

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }
}
