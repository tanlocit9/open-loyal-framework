<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Infrastructure\Repository;

use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Infrastructure\Repository\CustomerDetailsElasticsearchRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerDetailsElasticsearchRepositoryTest.
 */
class CustomerDetailsElasticsearchRepositoryTest extends TestCase
{
    /**
     * @var CustomerDetailsRepository|MockObject
     */
    private $customerDetailsRepository;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        // Heads up! MockRepository class is needed as PHPUnit does not mock inherited methods.
        // It's defined at the end of this file.
        $this->customerDetailsRepository = $this->createMock(MockRepository::class);
        $this->customerDetailsRepository->method('query')
            ->will($this->returnArgument(0));
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function it_produces_birthday_anniversary_query_with_correct_timestamps(): void
    {
        $method = new \ReflectionMethod(
            CustomerDetailsElasticsearchRepository::class,
            'findByBirthdayAnniversary'
        );
        $query = $method->invoke(
            $this->customerDetailsRepository,
            new \DateTime('today'),
            new \DateTime('tomorrow')
        );

        $this->assertArrayHasKey('bool', $query);
        $this->assertArrayHasKey('must', $query['bool']);

        foreach ($query['bool']['must'] as $searchTerm) {
            // Check if search is for active users only
            if (array_key_exists('term', $searchTerm)) {
                $this->assertTrue($searchTerm['term']['active']);

                continue;
            }

            // Check if search term properly defines ranges of birthday timestamps
            if (array_key_exists('bool', $searchTerm)) {
                $this->assertArrayHasKey('should', $searchTerm['bool']);

                foreach ($searchTerm['bool']['should'] as $rangeTerm) {
                    $this->assertHasCorrectRanges($rangeTerm);
                }

                continue;
            }
        }
    }

    /**
     * @param array $rangeTerm
     */
    private function assertHasCorrectRanges(array $rangeTerm): void
    {
        $this->assertArrayHasKey('range', $rangeTerm);
        $this->assertArrayHasKey('birthDate', $rangeTerm['range']);

        $range = $rangeTerm['range']['birthDate'];
        $this->assertArrayHasKey('gte', $range);
        $this->assertArrayHasKey('lt', $range);

        // Assert the days are exactly 1 day long with delta of 1 hour.
        // Heads up! They can be up to an hour longer or shorter due to DST and up to 15 seconds
        // longer or shorter due to leap seconds and timezone adjustments in 20th century.
        $this->assertEquals(
            86400,
            $range['lt'] - $range['gte'],
            sprintf(
                'Failed day-length check for timestamps: %d - %d = %d not 86400.',
                $range['lt'],
                $range['gte'],
                $range['lt'] - $range['gte']
            ),
            3600
        );
    }
}

class MockRepository extends CustomerDetailsElasticsearchRepository
{
    public function query(array $query): array
    {
        return parent::query($query);
    }
}
