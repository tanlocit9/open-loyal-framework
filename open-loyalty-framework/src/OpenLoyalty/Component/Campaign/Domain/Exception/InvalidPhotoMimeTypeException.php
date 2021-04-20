<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Exception;

/**
 * Class InvalidPhotoMimeTypeException.
 */
class InvalidPhotoMimeTypeException extends \DomainException
{
    /**
     * @param string $types
     *
     * @return InvalidPhotoMimeTypeException
     */
    public static function create(string $types): self
    {
        return new self(
            sprintf(
                'Given file has invalid mime type. Expected types: %s',
                $types
            )
        );
    }
}
