<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\Core\Domain\Model\LabelMultiplier;
use OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Type\LabelMultipliersJsonArrayDoctrineType;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;

/**
 * Class LabelMultipliersJsonArrayDoctrineTypeTest.
 */
class LabelMultipliersJsonArrayDoctrineTypeTest extends TestCase
{
    /**
     * @var Type
     */
    protected $type;

    /**
     * @var AbstractPlatform
     */
    protected $platform;

    /**
     * {@inheritdoc}
     *
     * @throws DBALException
     */
    protected function setUp()
    {
        if (!Type::hasType('label_multipliers_json_array')) {
            Type::addType('label_multipliers_json_array', LabelMultipliersJsonArrayDoctrineType::class);
        }

        $this->type = Type::getType('label_multipliers_json_array');
        $this->platform = $this->getMockForAbstractClass(AbstractPlatform::class);
    }

    /**
     * @test
     */
    public function testConvertsToDatabaseValue()
    {
        $data = [
            new LabelMultiplier('color', 'blue', 10),
        ];

        $value = $this->type->convertToDatabaseValue($data, $this->platform);
        $expected = '[{"key":"color","value":"blue","multiplier":10}]';

        $this->assertInternalType('string', $value);
        $this->assertEquals($expected, $value);
    }

    /**
     * @test
     */
    public function testConvertsToPHPValue()
    {
        $data = '[{"key":"color","value":"blue","multiplier":10}]';
        $converted = $this->type->convertToPHPValue($data, $this->platform);

        $this->assertInternalType('array', $converted);
        $this->assertTrue(isset($converted[0]));
        $this->assertTrue($converted[0] instanceof LabelMultiplier);
        $this->assertEquals('color', $converted[0]->getKey());
        $this->assertEquals('blue', $converted[0]->getValue());
        $this->assertEquals(10, $converted[0]->getMultiplier());
    }
}
