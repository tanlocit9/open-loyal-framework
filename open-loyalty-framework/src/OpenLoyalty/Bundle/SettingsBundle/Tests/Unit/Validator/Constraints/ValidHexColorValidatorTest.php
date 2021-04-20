<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\Validator\Constraints;

use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints\ValidHexColor;
use OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints\ValidHexColorValidator;
use OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints\ValidJson;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * Class ValidHexColorValidatorTest.
 */
class ValidHexColorValidatorTest extends ConstraintValidatorTestCase
{
    const SAMPLE_SETTINGS_KEY = 'test_key';

    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new ValidHexColorValidator();
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function throws_exception_when_invalid_type()
    {
        $this->validator->validate(new StringSettingEntry(self::SAMPLE_SETTINGS_KEY, ''), new ValidJson());
    }

    /**
     * @test
     */
    public function test_null_is_valid()
    {
        $this->validator->validate(null, new ValidHexColor());
        $this->assertNoViolation();
    }

    /**
     * @test
     * @dataProvider hexColorProvider
     *
     * @param string $input
     * @param bool   $isValid
     */
    public function test_valid_or_invalid(string $input, bool $isValid)
    {
        $constraint = new ValidHexColor();

        $this->validator->validate(new StringSettingEntry(self::SAMPLE_SETTINGS_KEY, $input), $constraint);

        if ($isValid) {
            $this->assertNoViolation();
        } else {
            $this
                ->buildViolation($constraint->message)
                ->assertRaised()
            ;
        }
    }

    /**
     * @return array
     */
    public function hexColorProvider()
    {
        return [
            ['#0AB123', true],
            ['#0ab123', true],
            ['', true],
            ['#123', true],
            ['#ABCZZZ', false],
            ['AAA000', false],
            ['012', false],
            ['#123123123', false],
            ['#12', false],
            ['#', false],
        ];
    }
}
