<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Exception;

/**
 * Class EmptyPhotoOriginalNameException.
 */
class EmptyPhotoOriginalNameException extends \DomainException
{
    /**
     * @return EmptyPhotoOriginalNameException
     */
    public static function create(): self
    {
        return new self('Photo original name is required and can not be empty!');
    }
}
