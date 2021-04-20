<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain;

use OpenLoyalty\Component\Campaign\Domain\Exception\EmptyPhotoOriginalNameException;
use OpenLoyalty\Component\Campaign\Domain\PhotoOriginalName;
use PHPUnit\Framework\TestCase;

/**
 * Class PhotoOriginalNameTest.
 */
class PhotoOriginalNameTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_when_name_is_empty(): void
    {
        $this->expectException(EmptyPhotoOriginalNameException::class);
        new PhotoOriginalName('');
    }

    /**
     * @test
     */
    public function it_return_original_photo_name_when_object_is_converted_to_string(): void
    {
        $photoName = new PhotoOriginalName('example.jpg');
        $this->assertSame('example.jpg', (string) $photoName);
    }
}
