<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain;

use OpenLoyalty\Component\Campaign\Domain\Exception\EmptyPhotoPathException;
use OpenLoyalty\Component\Campaign\Domain\PhotoPath;
use PHPUnit\Framework\TestCase;

/**
 * Class PhotoPathTest.
 */
class PhotoPathTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_when_path_is_empty(): void
    {
        $this->expectException(EmptyPhotoPathException::class);
        new PhotoPath('');
    }

    /**
     * @test
     */
    public function it_return_value_when_object_is_converted_to_string(): void
    {
        $path = new PhotoPath('path/to/file');
        $this->assertSame('campaign_photos/path/to/file', (string) $path);
    }
}
