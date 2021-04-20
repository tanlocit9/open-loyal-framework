<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Tests\Unit\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SortFilterTest.
 */
class SortFilterTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $sortFilter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())->method('getFieldNames')->willReturn(
            ['test', 'test1', 'test2']
        );

        $testClassName = 'TestClassName';
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $entityManager->expects($this->once())->method('getClassMetadata')->with($testClassName)->willReturn($metadata);

        /* @var MockObject|SortFilter sortFilter */
        $this->sortFilter = $this->getMockForTrait(SortFilter::class);
        $this->sortFilter->expects($this->once())->method('getEntityManager')->willReturn($entityManager);
        $this->sortFilter->expects($this->once())->method('getClassName')->willReturn($testClassName);
    }

    /**
     * @test
     */
    public function it_returns_a_column_name_that_exists_in_class()
    {
        $sortFilter = clone $this->sortFilter;
        $this->assertEquals('test', $sortFilter->validateSort('test'));
    }

    /**
     * @test
     */
    public function it_returns_a_column_name_that_exists_in_class_2()
    {
        $sortFilter = clone $this->sortFilter;
        $this->assertEquals('test2', $sortFilter->validateSort('test2'));
    }

    /**
     * @test
     */
    public function it_returns_a_column_first_name_because_defined_column_does_not_exists()
    {
        $sortFilter = clone $this->sortFilter;
        $this->assertEquals('test', $sortFilter->validateSort('100'));
    }
}
