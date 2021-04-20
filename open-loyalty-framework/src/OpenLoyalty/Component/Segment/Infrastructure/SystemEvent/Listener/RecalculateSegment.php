<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Infrastructure\SystemEvent\Listener;

use OpenLoyalty\Component\Segment\Domain\SystemEvent\SegmentChangedSystemEvent;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class RecalculateSegment.
 */
class RecalculateSegment
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * RecalculateSegment constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param SegmentChangedSystemEvent $event
     *
     * @throws \Exception
     */
    public function handle(SegmentChangedSystemEvent $event)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(array(
            'command' => 'oloy:segment:recreate',
            '--segmentId' => (string) $event->getSegmentId(),
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);
    }
}
