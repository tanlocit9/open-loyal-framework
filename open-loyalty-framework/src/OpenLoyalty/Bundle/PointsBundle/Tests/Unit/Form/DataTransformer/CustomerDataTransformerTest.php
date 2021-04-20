<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Tests\Unit\Form\DataTransformer;

use OpenLoyalty\Bundle\PointsBundle\Form\DataTransformer\CustomerDataTransformer;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerDataTransformerTest.
 */
class CustomerDataTransformerTest extends TestCase
{
    /**
     * @test
     * @dataProvider getTestData
     */
    public function it_transforms_correctly(string $text, array $data, string $expectedId): void
    {
        $customers = [];
        foreach ($data as $datum) {
            $customers[] = $this->getCustomerDetails($datum['id'], $datum['fields']);
        }
        $transformer = new CustomerDataTransformer($this->getCustomerDetailsRepository($customers));
        $customer = $transformer->reverseTransform($text);

        $this->assertInstanceOf(CustomerDetails::class, $customer);
        $this->assertEquals($expectedId, (string) $customer->getId());
    }

    public function getTestData()
    {
        return [
            [
                '456',
                [
                    ['id' => '1', 'fields' => ['phone' => '123', 'email' => '1@oloy.com']],
                    ['id' => '2', 'fields' => ['phone' => '456', 'email' => '2@oloy.com']],
                    ['id' => '3', 'fields' => ['phone' => '789', 'email' => '3@oloy.com']],
                ],
                2,
            ],
            [
                '3@oloy.com',
                [
                    ['id' => '1', 'fields' => ['phone' => '123', 'email' => '1@oloy.com']],
                    ['id' => '2', 'fields' => ['phone' => '456', 'email' => '2@oloy.com']],
                    ['id' => '3', 'fields' => ['phone' => '789', 'email' => '3@oloy.com']],
                ],
                3,
            ],
            [
                '1',
                [
                    ['id' => '1', 'fields' => ['phone' => '123', 'email' => '1@oloy.com']],
                    ['id' => '2', 'fields' => ['phone' => '456', 'email' => '2@oloy.com']],
                    ['id' => '3', 'fields' => ['phone' => '789', 'email' => '3@oloy.com']],
                ],
                1,
            ],
            [
                '2',
                [
                    ['id' => '1', 'fields' => ['phone' => '2', 'email' => '1@oloy.com']],
                    ['id' => '2', 'fields' => ['phone' => '456', 'email' => '2@oloy.com']],
                    ['id' => '3', 'fields' => ['phone' => '789', 'email' => '3@oloy.com']],
                ],
                2,
            ],
        ];
    }

    private function getCustomerDetailsRepository(array $returnValues)
    {
        $repo = $this->getMockBuilder(CustomerDetailsRepository::class)
            ->disableOriginalConstructor()->getMock();
        $repo->method('findByAnyCriteria')->willReturn($returnValues);

        return $repo;
    }

    private function getCustomerDetails(string $id, array $fields)
    {
        $customer = $this->getMockBuilder(CustomerDetails::class)
            ->disableOriginalConstructor()->getMock();
        $customer->method('getId')->willReturn($id);
        foreach ($fields as $key => $value) {
            $customer->method('get'.ucfirst($key))->willReturn($value);
        }

        return $customer;
    }
}
