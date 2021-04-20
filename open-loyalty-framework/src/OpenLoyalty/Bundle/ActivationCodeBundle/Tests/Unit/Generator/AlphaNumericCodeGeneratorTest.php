<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Tests\Unit\Generator;

use OpenLoyalty\Bundle\ActivationCodeBundle\Generator\AlphaNumericCodeGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Class AlphaNumericCodeGeneratorTest.
 */
class AlphaNumericCodeGeneratorTest extends TestCase
{
    /**
     * @var AlphaNumericCodeGenerator
     */
    protected $alphaNumCodeGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->alphaNumCodeGenerator = new AlphaNumericCodeGenerator();
    }

    /**
     * @test
     *
     * @dataProvider codeDataProvider
     *
     * @param string $objectType
     * @param string $objectId
     * @param int    $length
     * @param int    $expectedLength
     */
    public function it_returns_a_proper_code_length($objectType, $objectId, $length, $expectedLength)
    {
        $code = $this->alphaNumCodeGenerator->generate($objectType, $objectId, $length);
        $this->assertEquals($expectedLength, strlen($code));
    }

    /**
     * Code data provider.
     *
     * @return array
     */
    public function codeDataProvider()
    {
        return [
            ['User', '1234', 8, 8],
            ['User', '1234', 12, 12],
            ['User', '1234', 0, 128],
            ['User', '1234', -1, 128],
            ['User', '1234', 1, 1],
            ['Customer', '346364sDG235', 1, 1],
        ];
    }
}
