<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\EarningRuleBundle\Form\Type\BaseEarningRuleFormType;
use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRule;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Class BaseEarningRuleFormTypeTest.
 */
class BaseEarningRuleFormTypeTest extends TypeTestCase
{
    /**
     * @param EarningRule $rule
     *
     * @return ExecutionContextInterface|MockObject
     */
    protected function createMockContext(EarningRule $rule): MockObject
    {
        $parentFormMock = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $parentFormMock->method('getNormData')->willReturn($rule);

        $formMock = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $formMock->method('getParent')->willReturn($parentFormMock);

        $contextMock = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $contextMock->method('getObject')->willReturn($formMock);

        return $contextMock;
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_validate_rule_when_level_or_segments_is_not_defined()
    {
        $contextResultMock = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)
            ->disableOriginalConstructor()->getMock();

        $contextResultMock->method('atPath')->willReturnSelf();

        $object = new EarningRule();

        $contextMock = $this->createMockContext($object);
        $contextMock->expects($this->any())->method('buildViolation')->willReturn($contextResultMock);

        $base = new BaseEarningRuleFormType();
        $base->validateTarget([], $contextMock);

        $contextMock->getViolations();
    }

    /**
     * @test
     */
    public function it_validate_rule_when_level_is_defined()
    {
        $contextResultMock = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)
            ->disableOriginalConstructor()->getMock();

        $contextResultMock->method('atPath')->willReturnSelf();

        $object = new EarningRule();
        $object->setLevels([
            new Level(
                new LevelId('f99748f2-bf86-11e6-a4a6-cec0c932ce01'),
                0
            ),
        ]);

        $contextMock = $this->createMockContext($object);
        $contextMock->expects($this->never())->method('buildViolation')->willReturn($contextResultMock);

        $base = new BaseEarningRuleFormType();
        $base->validateTarget([], $contextMock);
    }
}
