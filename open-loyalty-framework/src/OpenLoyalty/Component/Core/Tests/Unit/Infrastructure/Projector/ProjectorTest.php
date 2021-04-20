<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Tests\Unit\Infrastructure\Projector;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Elasticsearch\Common\Exceptions\Conflict409Exception;
use OpenLoyalty\Component\Core\Infrastructure\Projector\Projector;
use PHPUnit\Framework\TestCase;

/**
 * Class ProjectorTest.
 */
class ProjectorTest extends TestCase
{
    /**
     * @test
     */
    public function it_passes_the_event_and_domain_message(): void
    {
        $testProjector = new TestProjector();
        $testEvent = new TestEvent();

        $this->assertFalse($testProjector->isCalled());

        $testProjector->handle($this->createDomainMessage($testEvent));

        $this->assertTrue($testProjector->isCalled());
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Core\Infrastructure\Projector\NotSynchronizedProjectionException
     */
    public function it_throws_exception_when_unable_to_handle_projection(): void
    {
        $testProjector = new ExceptionProjector();
        $testEvent = new TestEvent();

        $testProjector->handle($this->createDomainMessage($testEvent));
        $this->assertEquals(3, $testEvent->increment);
    }

    /**
     * @param TestEvent $event
     *
     * @return DomainMessage
     */
    private function createDomainMessage(TestEvent $event): DomainMessage
    {
        return DomainMessage::recordNow(1, 1, new Metadata([]), $event);
    }
}

/**
 * Class TestProjector.
 */
class TestProjector extends Projector
{
    /**
     * @var bool
     */
    private $isCalled = false;

    /**
     * @param TestEvent     $event
     * @param DomainMessage $domainMessage
     */
    public function applyTestEvent(TestEvent $event, DomainMessage $domainMessage)
    {
        $this->isCalled = true;
    }

    /**
     * @return bool
     */
    public function isCalled(): bool
    {
        return $this->isCalled;
    }
}

/**
 * Class ExceptionProjector.
 */
class ExceptionProjector extends Projector
{
    /**
     * @param TestEvent     $event
     * @param DomainMessage $domainMessage
     *
     * @throws Conflict409Exception
     */
    public function applyTestEvent(TestEvent $event, DomainMessage $domainMessage)
    {
        $event->testMethod();
        throw new Conflict409Exception();
    }
}

/**
 * Class TestEvent.
 */
class TestEvent
{
    /**
     * @var int
     */
    public $increment = 0;

    public function testMethod(): void
    {
        ++$this->increment;
    }
}
