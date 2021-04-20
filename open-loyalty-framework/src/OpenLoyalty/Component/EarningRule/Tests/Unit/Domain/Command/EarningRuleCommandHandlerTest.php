<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Tests\Unit\Domain\Command;

use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\Command\EarningRuleCommandHandler;
use OpenLoyalty\Component\EarningRule\Domain\Command\RemoveEarningRulePhoto;
use OpenLoyalty\Component\EarningRule\Domain\Command\SetEarningRulePhoto;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\EarningRule\Domain\Exception\EarningRuleDoesNotExistException;
use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto;
use PHPUnit\Framework\TestCase;

/**
 * Class SetEarningRulePhotoTest.
 */
class EarningRuleCommandHandlerTest extends TestCase
{
    const EARNING_RULE_UUID = 'a8dcb15f-2b71-444c-8265-664dd930d955';
    /**
     * @var EarningRuleRepository
     */
    private $rulesRepository;
    /**
     * @var EarningRuleCommandHandler
     */
    private $commandHandler;

    /**
     * @var EarningRule
     */
    private $earningRule;

    /**
     * Correct ID.
     *
     * @var string
     */
    private $earningRuleIdRight;

    /**
     * Incorrect ID.
     *
     * @var string
     */
    private $earningRuleIdWrong;

    public function setUp()
    {
        parent::setUp();

        $this->earningRuleIdRight = new EarningRuleId(self::EARNING_RULE_UUID);
        $this->earningRuleIdWrong = new EarningRuleId('7ed4263e-5c44-4221-8505-e784b3b4fd43');
        $earningRule = new EarningRule();
        $earningRule->setEarningRuleId($this->earningRuleIdRight);
        $this->earningRule = $earningRule;
        $earningRulePhoto = new EarningRulePhoto();
        $earningRulePhoto->setMime('image/png');
        $this->earningRule->setEarningRulePhoto($earningRulePhoto);

        $this->rulesRepository = $this->getMockBuilder(EarningRuleRepository::class)->getMock();
        $this->rulesRepository->expects($this->any())->method('byId')
            ->will(
                $this->returnValueMap(
                    [
                        [$this->earningRuleIdWrong, null],
                        [$this->earningRuleIdRight, $this->earningRule],
                    ]
                )
            );
        $uuidGenerator = $this->getMockBuilder(Version4Generator::class)->disableOriginalConstructor()->getMock();
        $uuidGenerator->expects($this->any())->method('generate')->will($this->returnValue(self::EARNING_RULE_UUID));

        $this->commandHandler = new EarningRuleCommandHandler($this->rulesRepository, $uuidGenerator);
    }

    /**
     * @test
     */
    public function it_uploads_photo_successfull()
    {
        $earningRulePhoto = new EarningRulePhoto();
        $earningRulePhoto->setMime('image/jpg');
        $setEarningRulePhotoCommand = new SetEarningRulePhoto($this->earningRuleIdRight, $earningRulePhoto);
        $this->commandHandler->handleSetEarningRulePhoto($setEarningRulePhotoCommand);
        $this->assertSame($earningRulePhoto, $this->earningRule->getEarningRulePhoto());
        $this->assertSame($this->earningRuleIdRight, $this->earningRule->getEarningRuleId());
    }

    /**
     * @test
     */
    public function it_fails_uploading_photo_caused_wrong_id_provided()
    {
        $this->expectException(EarningRuleDoesNotExistException::class);
        $earningRulePhoto = new EarningRulePhoto();
        $setEarningRulePhotoCommand = new SetEarningRulePhoto($this->earningRuleIdWrong, $earningRulePhoto);
        $this->commandHandler->handleSetEarningRulePhoto($setEarningRulePhotoCommand);
    }

    /**
     * @test
     */
    public function it_removes_photo_with_success()
    {
        $this->assertInstanceOf(EarningRulePhoto::class, $this->earningRule->getEarningRulePhoto());
        $removeEarningRulePhotoCommand = new RemoveEarningRulePhoto($this->earningRuleIdRight);
        $this->assertNotNull($this->earningRule->getEarningRulePhoto());
        $this->commandHandler->handleRemoveEarningRulePhoto($removeEarningRulePhotoCommand);
    }
}
