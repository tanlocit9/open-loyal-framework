<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Import;

use OpenLoyalty\Bundle\UserBundle\Import\CustomerXmlImportConverter;
use OpenLoyalty\Component\Import\Infrastructure\Validator\XmlNodeValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerXmlImportConverterTest.
 */
class CustomerXmlImportConverterTest extends TestCase
{
    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Import\Infrastructure\ImportConvertException
     */
    public function it_throw_validation_exception()
    {
        $xmlContent = file_get_contents(__DIR__.'/customers_2.xml');
        $converter = new CustomerXmlImportConverter();

        /** @var \SimpleXMLElement $element */
        $element = simplexml_load_string($xmlContent);

        $converter->convert($element->children()[0]);
    }

    /**
     * @test
     */
    public function it_convert_customer_xml_to_array()
    {
        $xmlContent = file_get_contents(__DIR__.'/customers_1.xml');
        $converter = new CustomerXmlImportConverter();

        /** @var \SimpleXMLElement $element */
        $element = simplexml_load_string($xmlContent);

        $customerData = $converter->convert($element->children()[0]);

        $expectedCustomerData = [
            'email' => 'jon@example.com',
            'firstName' => 'Jon',
            'lastName' => 'Doe',
            'gender' => 'male',
            'agreement1' => true,
            'agreement2' => false,
            'agreement3' => false,
            'birthDate' => \DateTime::createFromFormat(XmlNodeValidator::DATE_CONVERT_FORMAT, '2018-02-03'),
            'levelId' => 'e82c96cf-32a3-43bd-9034-4df343e50000',
            'posId' => 'adcf9c44-7401-43b1-98b9-59cff2ca3af7',
            'sellerId' => '00000000-0000-474c-b092-b0dd880c07e4',
            'active' => true,
            'sendActivationMail' => true,
            'loyaltyCardNumber' => '936592735',
            'phone' => '+48888888888',
            'company' => [
                'name' => 'Corporation',
                'nip' => '888-22-33-334',
            ],
            'address' => [
                'address1' => 'Dream street 33',
                'address2' => 'Room 2',
                'city' => 'New york',
                'country' => 'US',
                'postal' => '00-000',
                'street' => 'Dream street',
                'province' => 'Maryland',
            ],
            'labels' => [
                [
                    'key' => 'label_key_1',
                    'value' => 'label_value_1',
                ],
                [
                    'key' => 'label_key_2',
                    'value' => 'label_value_2',
                ],
            ],
        ];

        $this->assertEquals($customerData, $expectedCustomerData);
    }
}
