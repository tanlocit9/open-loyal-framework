<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Pos\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Pos\Domain\Command\UpdatePos;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;

/**
 * Class UpdatePosTest.
 */
class UpdatePosTest extends PosCommandHandlerTest
{
    /**
     * @test
     */
    public function it_updates_pos()
    {
        $handler = $this->createCommandHandler();
        $posId = new PosId('00000000-0000-0000-0000-000000000000');
        $posData = $this->getPosData();
        $this->poss[] = new Pos($posId, $posData);
        $posData['name'] = 'updated';
        $command = new UpdatePos($posId, $posData);
        $handler->handle($command);
        $pos = $this->inMemoryRepository->byId($posId);
        $this->assertNotNull($pos);
        $this->assertInstanceOf(Pos::class, $pos);
        $this->assertEquals('updated', $pos->getName());
    }

    protected function getPosData()
    {
        return [
            'name' => 'test',
            'identifier' => 'pos1',
            'description' => 'test',
            'location' => $this->getLocationData(),
        ];
    }

    protected function getLocationData()
    {
        return [
            'street' => 'Dmowskiego',
            'address1' => '21',
            'city' => 'Wrocław',
            'country' => 'PL',
            'postal' => '50-300',
            'province' => 'Dolnośląskie',
            'lat' => '11.11',
            'long' => '12.11',
        ];
    }
}
