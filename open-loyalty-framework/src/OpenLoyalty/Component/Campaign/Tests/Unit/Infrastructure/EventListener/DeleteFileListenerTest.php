<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Infrastructure\EventListener;

use Gaufrette\Filesystem;
use OpenLoyalty\Component\Campaign\Infrastructure\EventListener\DeleteFileListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DeleteFileListenerTest.
 */
class DeleteFileListenerTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $fileSystem;

    /**
     * @test
     */
    public function it_remove_file_on_invoke_event(): void
    {
        $this->fileSystem->expects($this->once())->method('delete');
        $listener = new DeleteFileListener($this->fileSystem);
        $listener->__invoke('path/to/file.jpg');
    }

    protected function setUp(): void
    {
        /* @var Filesystem|MockObject $filesystem */
        $this->fileSystem = $this->createMock(Filesystem::class);
    }
}
