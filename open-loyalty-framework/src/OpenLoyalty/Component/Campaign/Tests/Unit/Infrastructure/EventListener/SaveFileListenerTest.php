<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Tests\Unit\Infrastructure\EventListener;

use Gaufrette\Filesystem;
use OpenLoyalty\Component\Campaign\Infrastructure\EventListener\SaveFileListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SaveFileListenerTest.
 */
final class SaveFileListenerTest extends TestCase
{
    /**
     * @var Filesystem | MockObject
     */
    private $fileSystem;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fileSystem = $this->createMock(Filesystem::class);
    }

    /**
     * @test
     */
    public function it_write_file_in_path_after_event_is_handled(): void
    {
        $this->fileSystem->expects($this->once())->method('write');

        $listener = new SaveFileListener($this->fileSystem);
        $listener->__invoke('upload/test.png', __DIR__.'/fixture/save_file_listener_example.png');
    }
}
