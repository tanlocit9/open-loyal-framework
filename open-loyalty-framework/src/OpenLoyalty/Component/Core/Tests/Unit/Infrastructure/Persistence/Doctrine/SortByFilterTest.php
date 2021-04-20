<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Tests\Unit\Infrastructure\Persistence\Doctrine;

use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use PHPUnit\Framework\TestCase;

/**
 * Class SortByFilterTest.
 */
class SortByFilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_a_correct_sorting_direction()
    {
        /** @var SortByFilter $sortByFilter */
        $sortByFilter = $this->getMockForTrait(SortByFilter::class);
        $this->assertEquals('ASC', $sortByFilter->validateSortBy('ASC'));
        $this->assertEquals('DESC', $sortByFilter->validateSortBy('DESC'));
        $this->assertEquals('ASC', $sortByFilter->validateSortBy('ASDF'));
    }
}
