<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\Type\PhotoIdDoctrineType;
use PHPUnit\Framework\TestCase;

class PhotoIdDoctrineTypeTest extends TestCase
{
    /**
     * @var PhotoIdDoctrineType
     */
    private $type;

    /**
     * @var AbstractPlatform
     */
    private $platform;

    protected function setUp(): void
    {
        parent::setUp();

        if (!PhotoIdDoctrineType::hasType('photo_id')) {
            PhotoIdDoctrineType::addType('photo_id', PhotoIdDoctrineType::class);
        }
        $this->type = PhotoIdDoctrineType::getType('photo_id');
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    /**
     * @test
     */
    public function it_convert_string_value_to_photo_id_object_when_value_is_converted_to_php_value(): void
    {
        $actual = $this->type->convertToPHPValue('00000000-0000-0000-0000-4df343e5fd93', $this->platform);
        $this->assertInstanceOf(PhotoId::class, $actual);
    }

    /**
     * @test
     */
    public function it_return_null_when_value_is_null_and_converted_to_php_value(): void
    {
        $actual = $this->type->convertToPHPValue(null, $this->platform);
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function it_return_same_object_when_value_is_photo_id_and_is_converted_to_php_value(): void
    {
        $photoId = new PhotoId('00000000-0000-0000-0000-4df343e5fd93');
        $actual = $this->type->convertToPHPValue($photoId, $this->platform);
        $this->assertSame($photoId, $actual);
    }

    /**
     * @test
     */
    public function it_return_photo_id_on_name(): void
    {
        $this->assertSame('photo_id', $this->type->getName());
    }

    /**
     * @test
     */
    public function it_return_value_when_object_is_converted_to_database_value(): void
    {
        $photoId = new PhotoId('00000000-0000-0000-0000-4df343e5fd93');
        $actual = $this->type->convertToDatabaseValue($photoId, $this->platform);
        $this->assertSame('00000000-0000-0000-0000-4df343e5fd93', $actual);
    }

    /**
     * @test
     */
    public function it_return_null_when_value_is_null_and_is_converted_to_database_value(): void
    {
        $actual = $this->type->convertToDatabaseValue(null, $this->platform);
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function it_return_null_when_value_is_not_photo_id_object_and_is_converted_to_database_value(): void
    {
        $actual = $this->type->convertToDatabaseValue(new \DateTime(), $this->platform);
        $this->assertNull($actual);
    }
}
