<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Tests\Unit\Model;

use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto;
use PHPUnit\Framework\TestCase;

class EarningRulePhotoTest extends TestCase
{
    /**
     * @var EarningRulePhoto
     */
    private $earningRulePhoto;

    public function setUp()
    {
        $this->earningRulePhoto = new EarningRulePhoto();
        parent::setUp();
    }

    public function testIsRightPath()
    {
        $path = 'Some\Sample\Path';
        $this->assertNull($this->earningRulePhoto->getPath());
        $this->earningRulePhoto->setPath($path);
        $this->assertSame($path, $this->earningRulePhoto->getPath());
    }

    public function testIsRightOriginalName()
    {
        $originalName = 'original.name.png';
        $this->assertNull($this->earningRulePhoto->getOriginalName());
        $this->earningRulePhoto->setOriginalName($originalName);
        $this->assertSame($originalName, $this->earningRulePhoto->getOriginalName());
    }

    public function testIsRightMime()
    {
        $mime = 'image/png';
        $this->assertNull($this->earningRulePhoto->getMime());
        $this->earningRulePhoto->setMime($mime);
        $this->assertSame($mime, $this->earningRulePhoto->getMime());
    }
}
