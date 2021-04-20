<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Exception;

/**
 * Class EmptyPhotoPathException.
 */
class EmptyPhotoPathException extends \DomainException
{
    /**
     * @return EmptyPhotoPathException
     */
    public static function create(): self
    {
        return new self('Photo path is required and can not be empty!');
    }
}
