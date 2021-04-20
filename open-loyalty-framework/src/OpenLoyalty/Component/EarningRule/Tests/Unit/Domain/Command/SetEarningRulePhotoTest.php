<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\EarningRule\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\EarningRule\Domain\Command\SetEarningRulePhoto;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto;
use PHPUnit\Framework\TestCase;

/**
 * Class SetEarningRulePhotoTest.
 */
class SetEarningRulePhotoTest extends TestCase
{
    const SAMPLE_UUID = '6d744583-4f8a-417a-9d00-289ce9aca8e1';

    /**
     * @var SetEarningRulePhoto
     */
    private $setEarningRulePhoto;

    public function setUp()
    {
        parent::setUp();
        $earningRuleId = new EarningRuleId(self::SAMPLE_UUID);
        $earningRulePhoto = new EarningRulePhoto();
        $this->setEarningRulePhoto = new SetEarningRulePhoto($earningRuleId, $earningRulePhoto);
    }

    /**
     * @test
     */
    public function it_returns_right_interface()
    {
        $this->assertInstanceOf(EarningRulePhoto::class, $this->setEarningRulePhoto->getEarningRulePhoto());
    }

    /**
     * @test
     */
    public function it_returns_same_id_provided()
    {
        $this->assertSame(self::SAMPLE_UUID, $this->setEarningRulePhoto->getEarningRuleId()->__toString());
    }
}
