<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain;

use OpenLoyalty\Component\Campaign\Domain\Exception\InvalidPhotoMimeTypeException;
use OpenLoyalty\Component\Campaign\Domain\PhotoMimeType;
use PHPUnit\Framework\TestCase;

/**
 * Class PhotoMimeTypeTest.
 */
class PhotoMimeTypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_when_file_has_not_supported_mime_type(): void
    {
        $this->expectException(InvalidPhotoMimeTypeException::class);
        new PhotoMimeType('image/psd');
    }

    /**
     * @test
     */
    public function it_return_photo_mime_type_when_object_is_converted_to_string(): void
    {
        $mimeType = new PhotoMimeType('image/gif');
        $this->assertSame('image/gif', (string) $mimeType);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_mime_type_is_empty(): void
    {
        $this->expectException(InvalidPhotoMimeTypeException::class);
        new PhotoMimeType('');
    }
}
