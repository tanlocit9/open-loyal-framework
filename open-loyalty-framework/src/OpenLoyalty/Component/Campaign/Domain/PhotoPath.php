<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use OpenLoyalty\Component\Campaign\Domain\Exception\EmptyPhotoPathException;

/**
 * Class PhotoPath.
 */
class PhotoPath
{
    private const PHOTO_DIR = 'campaign_photos/';

    /**
     * @var string
     */
    private $value;

    /**
     * PhotoPath constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        if (empty($path)) {
            throw EmptyPhotoPathException::create();
        }

        $this->value = self::PHOTO_DIR.$path;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
