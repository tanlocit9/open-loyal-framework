<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\EarningRule\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\EarningRule\Domain\Command\EarningRuleCommand;
use OpenLoyalty\Component\EarningRule\Domain\Command\RemoveEarningRulePhoto;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveEarningRulePhotoTest.
 */
class RemoveEarningRulePhotoTest extends TestCase
{
    public function testIsRightInterfaceImplemented()
    {
        $this->assertInstanceOf(EarningRuleCommand::class, new RemoveEarningRulePhoto(new EarningRuleId('a8dcb15f-2b71-444c-8265-664dd930d955')));
    }

    /**
     * @test
     */
    public function it_returns_same_earning_rule_id()
    {
        $uuid = 'a8dcb15f-2b71-444c-8265-664dd930d955';
        $removeCommand = new RemoveEarningRulePhoto(new EarningRuleId($uuid));
        $this->assertSame($uuid, $removeCommand->getEarningRuleId()->__toString());
    }
}
