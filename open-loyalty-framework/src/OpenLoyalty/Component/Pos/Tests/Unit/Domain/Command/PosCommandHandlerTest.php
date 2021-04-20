<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Pos\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Pos\Domain\Command\PosCommandHandler;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class PosCommandHandlerTest.
 */
abstract class PosCommandHandlerTest extends TestCase
{
    protected $inMemoryRepository;

    protected $poss = [];

    public function setUp()
    {
        $poss = &$this->poss;
        $this->inMemoryRepository = $this->getMockBuilder(PosRepository::class)->getMock();
        $this->inMemoryRepository->method('save')->with($this->isInstanceOf(Pos::class))->will(
            $this->returnCallback(function ($pos) use (&$poss) {
                $poss[] = $pos;

                return $pos;
            })
        );
        $this->inMemoryRepository->method('findAll')->with()->will(
            $this->returnCallback(function () use (&$poss) {
                return $poss;
            })
        );
        $this->inMemoryRepository->method('byId')->with($this->isInstanceOf(PosId::class))->will(
            $this->returnCallback(function ($id) use (&$poss) {
                /** @var Pos $pos */
                foreach ($poss as $pos) {
                    if ($pos->getPosId()->__toString() == $id->__toString()) {
                        return $pos;
                    }
                }

                return;
            })
        );
    }

    protected function createCommandHandler()
    {
        return new PosCommandHandler(
            $this->inMemoryRepository
        );
    }
}
