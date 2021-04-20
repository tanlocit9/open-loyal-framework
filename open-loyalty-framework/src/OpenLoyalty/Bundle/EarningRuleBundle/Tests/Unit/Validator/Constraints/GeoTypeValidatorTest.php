<?php

namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Unit\Validator\Constraints;

use OpenLoyalty\Bundle\EarningRuleBundle\Validator\Constraints\GeoTypeValidator;
use OpenLoyalty\Bundle\EarningRuleBundle\Validator\Constraints\GeoType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class GeoTypeValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        return new GeoTypeValidator($translator);
    }

    /**
     * @test
     */
    public function test_valid(): void
    {
        $constraint = new GeoType();
        $this->validator->validate(38.897778, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function test_comma(): void
    {
        $constraint = new GeoType();
        $this->validator->validate('38,897778', $constraint);
        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function test_zero(): void
    {
        $constraint = new GeoType();
        $this->validator->validate('0', $constraint);
        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function test_not_valid(): void
    {
        $constraint = new GeoType();
        $this->validator->validate('test value', $constraint);
        $this
            ->buildViolation(null)
            ->assertRaised()
        ;
    }
}
