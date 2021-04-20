<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Tests\Unit\Generator;

use OpenLoyalty\Bundle\ActivationCodeBundle\Generator\AlphaNumericCodeGenerator;
use OpenLoyalty\Bundle\ActivationCodeBundle\Generator\NumericCodeGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class NumericCodeGeneratorTest.
 */
class NumericCodeGeneratorTest extends TestCase
{
    /**
     * @var NumericCodeGenerator
     */
    protected $numCodeGenerator;

    /**
     * @var AlphaNumericCodeGenerator|MockObject
     */
    protected $alphaNumCodeGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        /* @var AlphaNumericCodeGenerator|MockObject $alphaNumCodeGenerator */
        $this->alphaNumCodeGenerator = $this->getMockBuilder(AlphaNumericCodeGenerator::class)->getMock();
        $this->numCodeGenerator = new NumericCodeGenerator($this->alphaNumCodeGenerator);
    }

    /**
     * @test
     *
     * @dataProvider codeDataProvider
     *
     * @param int    $length
     * @param string $alphaNumCode
     */
    public function it_generates_a_proper_code_length($length, $alphaNumCode)
    {
        $this->alphaNumCodeGenerator->method('generate')->willReturn($alphaNumCode);
        $code = $this->numCodeGenerator->generate('1', '2', $length);
        $this->assertEquals($length, strlen($code), 'Wrong length for a code: '.$code);
    }

    /**
     * @test
     */
    public function it_generates_a_numeric_code()
    {
        $this->alphaNumCodeGenerator->method('generate')->willReturn(hash('sha512', 'DDDDDDDD'));
        $code = $this->numCodeGenerator->generate('1', '2', 8);
        $this->assertTrue(is_numeric($code), 'The code is not a number: '.$code);
        $this->assertTrue(is_integer($code), 'The code is not an integer: '.$code);
    }

    /**
     * Codes data provider.
     *
     * @return array
     */
    public function codeDataProvider()
    {
        return [
            [8, hash('sha512', 'DDDDDDDD')],
            [8, hash('sha512', '12345678')],
            [8, hash('sha512', '1234567891023123')],
            [2, hash('sha512', 'F2')],
            [4, hash('sha512', 'SG2345GDHJ74DHE365')],
            [4, 'ABCDEFGHIJKLMNOPRSTUWXYZ123'],
            [12, 'ABC'],
        ];
    }
}
