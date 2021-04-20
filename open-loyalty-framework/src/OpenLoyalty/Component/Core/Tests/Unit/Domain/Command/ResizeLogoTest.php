<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Tests\Unit\Domain\Command;

use OpenLoyalty\Bundle\SettingsBundle\Model\Logo;
use OpenLoyalty\Component\Core\Domain\Command\LogoCommand;
use OpenLoyalty\Component\Core\Domain\Command\ResizeLogo;
use PHPUnit\Framework\TestCase;

/**
 * Class ResizeLogoTest.
 */
class ResizeLogoTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_same_logo_and_type()
    {
        $logo = new Logo();
        $type = 'small-logo';

        $command = new ResizeLogo($logo, $type);
        $this->assertSame($command->getLogo(), $logo);
        $this->assertSame($command->getType(), $type);
    }

    /**
     * @test
     */
    public function it_implements_right_interface()
    {
        $logo = new Logo();
        $type = 'small-logo';

        $command = new ResizeLogo($logo, $type);
        $this->assertInstanceOf(LogoCommand::class, $command);
    }
}
