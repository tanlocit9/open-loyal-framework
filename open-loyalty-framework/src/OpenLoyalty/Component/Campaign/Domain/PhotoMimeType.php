<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use OpenLoyalty\Component\Campaign\Domain\Exception\InvalidPhotoMimeTypeException;

/**
 * Class PhotoMimeType.
 */
class PhotoMimeType
{
    private const SUPPORTED_MIME_TYPES = ['image/gif', 'image/jpg', 'image/png', 'image/jpeg'];

    /**
     * @var string
     */
    private $value;

    /**
     * PhotoMimeType constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (empty($type) || !$this->expectedMimeType($type)) {
            throw InvalidPhotoMimeTypeException::create(implode(', ', PhotoMimeType::SUPPORTED_MIME_TYPES));
        }

        $this->value = $type;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function expectedMimeType(string $type): bool
    {
        return in_array($type, self::SUPPORTED_MIME_TYPES);
    }
}
