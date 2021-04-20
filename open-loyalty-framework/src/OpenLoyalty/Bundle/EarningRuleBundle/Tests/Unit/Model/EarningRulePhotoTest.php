<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Unit\Model;

use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRulePhoto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EarningRulePhotoTest extends TestCase
{
    /** @var EarningRulePhoto */
    private $earningRulePhoto;

    public function setUp()
    {
        parent::setUp();

        $this->earningRulePhoto = new EarningRulePhoto();
    }

    /**
     * @test
     */
    public function it_has_right_interface_implemented()
    {
        $this->assertInstanceOf(\OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto::class, $this->earningRulePhoto);
    }

    /**
     * @test
     */
    public function it_returns_uploaded_file()
    {
        $file = new UploadedFile(__FILE__, 'original.name.png');
        $this->assertEmpty($this->earningRulePhoto->getFile());

        $this->earningRulePhoto->setFile($file);
        $this->assertSame($file, $this->earningRulePhoto->getFile());
        $this->assertEquals(__FILE__, $this->earningRulePhoto->getFile()->getRealPath());
        $this->assertEquals('original.name.png', $this->earningRulePhoto->getFile()->getClientOriginalName());
    }

    /**
     * @test
     */
    public function it_returns_right_interface()
    {
        $returned = $this->earningRulePhoto->setFile(new UploadedFile(__FILE__, 'some.name.png'));
        $this->assertInstanceOf(EarningRulePhoto::class, $returned);
    }
}
