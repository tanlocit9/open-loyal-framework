<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Tests\Unit\Infrastructure\Validator;

use OpenLoyalty\Component\Import\Infrastructure\Validator\XmlNodeValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class XmlNodeValidatorTest.
 */
class XmlNodeValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function it_validate_required_success()
    {
        $node = new \SimpleXMLElement('<transaction><item>Value</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate($node, 'item', ['required' => true]);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_not_required_failed()
    {
        $node = new \SimpleXMLElement('<transaction><item2>Value</item2></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate($node, 'item', ['required' => true]);

        $this->assertTrue($result == 'item is required node');
    }

    /**
     * @test
     */
    public function it_validate_date_time_format_success()
    {
        $node = new \SimpleXMLElement('<transaction><item>2005-08-15T15:52:01+00:00</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::DATE_TIME_FORMAT]
        );

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_date_time_format_failed()
    {
        $node = new \SimpleXMLElement('<transaction><item>2005-08-15</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::DATE_TIME_FORMAT]
        );

        $this->assertTrue($result == 'item has invalid date format (ATOM required)');
    }

    /**
     * @test
     */
    public function it_validate_decimal_format_success()
    {
        $node = new \SimpleXMLElement('<transaction><item>233.55</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::DECIMAL_FORMAT]
        );

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_decimal_format_failed()
    {
        $node = new \SimpleXMLElement('<transaction><item>445,33</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::DECIMAL_FORMAT]
        );

        $this->assertTrue($result == 'item should be number value');
    }

    /**
     * @test
     */
    public function it_validate_integer_format_success()
    {
        $node = new \SimpleXMLElement('<transaction><item>44</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::INTEGER_FORMAT]
        );

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_integer_format_failed()
    {
        $node = new \SimpleXMLElement('<transaction><item>445.44</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::INTEGER_FORMAT]
        );

        $this->assertTrue($result == 'item should be integer value');
    }

    /**
     * @test
     */
    public function it_validate_valid_const_format_when_empty()
    {
        $node = new \SimpleXMLElement('<transaction><item></item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::VALID_CONST_FORMAT]
        );

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_valid_const_format_success()
    {
        $node = new \SimpleXMLElement('<transaction><item>const_1</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            [
                'required' => true,
                'format' => XmlNodeValidator::VALID_CONST_FORMAT,
                'values' => ['const_1', 'const_2'],
            ]
        );

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_valid_const_format_failed()
    {
        $node = new \SimpleXMLElement('<transaction><item>const_3</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            [
                'required' => true,
                'format' => XmlNodeValidator::VALID_CONST_FORMAT,
                'values' => ['const_1', 'const_2'],
            ]
        );

        $this->assertTrue($result == 'item should one of (const_1, const_2)');
    }

    /**
     * @test
     */
    public function it_validate_valid_bool_format_success()
    {
        $node = new \SimpleXMLElement('<transaction><item>true</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            [
                'required' => true,
                'format' => XmlNodeValidator::BOOL_FORMAT,
            ]
        );

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_valid_bool_format_failed()
    {
        $node = new \SimpleXMLElement('<transaction><item>true3</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            [
                'required' => true,
                'format' => XmlNodeValidator::BOOL_FORMAT,
            ]
        );

        $this->assertTrue($result == 'item should one of (true, false)');
    }

    /**
     * @test
     */
    public function it_validate_date_format_success()
    {
        $node = new \SimpleXMLElement('<transaction><item>2005-08-15</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::DATE_FORMAT]
        );

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_date_format_failed()
    {
        $node = new \SimpleXMLElement('<transaction><item>2005-08-15T15:52:01+00:00</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::DATE_FORMAT]
        );

        $this->assertTrue($result == 'item has invalid date format (Y-m-d required)');
    }

    /**
     * @test
     */
    public function it_validate_uuid_format_success()
    {
        $node = new \SimpleXMLElement('<transaction><item>000096cf-32a3-43bd-9034-4df343e5fd94</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::UUID_FORMAT]
        );

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_validate_uuid_format_failed()
    {
        $node = new \SimpleXMLElement('<transaction><item>000096cf-4df343e5fd94</item></transaction>');

        $nodeValidator = new XmlNodeValidator();
        $result = $nodeValidator->validate(
            $node,
            'item',
            ['required' => true, 'format' => XmlNodeValidator::UUID_FORMAT]
        );

        $this->assertTrue($result == 'item should be UUID');
    }
}
