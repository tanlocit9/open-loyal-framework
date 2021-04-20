<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\Tests\Unit\CSVGenerator;

use OpenLoyalty\Bundle\CoreBundle\CSVGenerator\Mapper;
use OpenLoyalty\Bundle\CoreBundle\CSVGenerator\MapperInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class MapperTest.
 */
class MapperTest extends TestCase
{
    /** @var MapperInterface */
    private $mapper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->mapper = new Mapper(
            [
                'purchasedAt' => ['conversion' => 'timeToString'],
                'someBoolean' => ['conversion' => 'boolToString'],
            ]
        );
    }

    /**
     * @test
     */
    public function it_implements_right_interface()
    {
        $this->assertInstanceOf(MapperInterface::class, $this->mapper);
    }

    /**
     * @test
     */
    public function it_maps_time_to_string_date()
    {
        $mappedValue = $this->mapper->map('purchasedAt', 1530131714);
        $this->assertNotEquals(1530131714, $mappedValue);
        $date = date('Y-m-d', strtotime($mappedValue));
        $this->assertEquals('2018-06-27', $date);
    }

    /**
     * @test
     */
    public function it_maps_boolean_to_string()
    {
        $mappedValue = $this->mapper->map('someBoolean', true);
        $this->assertNotSame(true, $mappedValue);
        $this->assertSame('1', $mappedValue);
    }

    /**
     * @test
     */
    public function it_does_not_map_if_map_does_not_exist()
    {
        $mappedValue = $this->mapper->map('unsetField', 'original-data');
        $this->assertSame('original-data', $mappedValue);
    }
}
