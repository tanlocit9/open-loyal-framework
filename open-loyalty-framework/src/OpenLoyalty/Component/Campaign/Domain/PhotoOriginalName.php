<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use OpenLoyalty\Component\Campaign\Domain\Exception\EmptyPhotoOriginalNameException;

/**
 * Class PhotoPath.
 */
class PhotoOriginalName
{
    /**
     * @var string
     */
    private $value;

    /**
     * PhotoPath constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        if (empty($name)) {
            throw EmptyPhotoOriginalNameException::create();
        }

        $this->value = $name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
