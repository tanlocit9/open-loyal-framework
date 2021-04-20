<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Unit\Model;

use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto;
use PHPUnit\Framework\TestCase;

/**
 * Class EarningRuleTest.
 */
class EarningRuleTest extends TestCase
{
    const EARNING_RULE_ID = '3a40b784-913f-45ee-8646-a78b2b4f5cef';

    /**
     * @var EarningRule
     */
    private $earningRuleObject;

    public function setUp()
    {
        parent::setUp();
        $this->earningRuleObject = new EarningRule();

        $photo = new EarningRulePhoto();
        $photo->setPath('some/path/to/photo.png');
        $photo->setMime('some/mime');
        $photo->setOriginalName('photo.png');
        $this->earningRuleObject->setEarningRulePhoto($photo);
    }

    /**
     * @test
     */
    public function it_returns_true_if_photo_exists()
    {
        $this->assertTrue($this->earningRuleObject->hasEarningRulePhoto());
    }

    /**
     * @test
     */
    public function it_returns_false_if_photo_does_not_exist()
    {
        $this->earningRuleObject->removeEarningRulePhoto();
        $this->assertFalse($this->earningRuleObject->hasEarningRulePhoto());
    }

    /**
     * @test
     */
    public function it_returns_false_if_photo_object_is_empty()
    {
        $this->earningRuleObject->removeEarningRulePhoto();
        $this->earningRuleObject->setEarningRulePhoto(new EarningRulePhoto());
        $this->assertFalse($this->earningRuleObject->hasEarningRulePhoto());
    }
}
