<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\EarningRuleBundle\Form\Type\CreateEarningRuleFormType;
use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableProvider;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class CreateEarningRuleFormTypeTest.
 */
class CreateEarningRuleFormTypeTest extends TypeTestCase
{
    private $validator;

    protected function setUp()
    {
        $this->validator = $this->getMockBuilder(
            ValidatorInterface::class
        )->getMock();
        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));
        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()->getMock();
        $metadata->method('addConstraint')->willReturn(true);
        $metadata->method('addPropertyConstraint')->willReturn(true);

        $this->validator->method('getMetadataFor')->willReturn(
            $metadata
        );

        parent::setUp();
    }

    protected function getExtensions()
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $type = new CreateEarningRuleFormType(
            new StoppableProvider(),
            $translator
        );

        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension($this->validator),
        );
    }

    /**
     * @test
     */
    public function it_custom_event_limit()
    {
        $form = $this->factory->create(CreateEarningRuleFormType::class);

        //year
        $form->submit(self::getDataForCustomEventLimit('1 year'));
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        //3months
        $form = $this->factory->create(CreateEarningRuleFormType::class);
        $form->submit(self::getDataForCustomEventLimit('3 months'));
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        //6months
        $form = $this->factory->create(CreateEarningRuleFormType::class);
        $form->submit(self::getDataForCustomEventLimit('6 months'));
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        //forever
        $form = $this->factory->create(CreateEarningRuleFormType::class);
        $form->submit(self::getDataForCustomEventLimit('forever'));
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
    }

    /**
     * @test
     */
    public function it_has_valid_data_when_creating_new_event_earning_rule()
    {
        $formData = array_merge($this->getMainData(), [
            'type' => EarningRule::TYPE_EVENT,
            'eventName' => 'test event',
            'pointsAmount' => 100,
        ]);

        $form = $this->factory->create(CreateEarningRuleFormType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    /**
     * @test
     */
    public function it_has_valid_data_when_creating_new_event_earning_multiply_rule()
    {
        $formData = array_merge($this->getMainData(), [
            'type' => EarningRule::TYPE_MULTIPLY_BY_PRODUCT_LABELS,
            'labelMultipliers' => [
                [
                    'key' => 'test',
                    'value' => 'example',
                    'multiplier' => 1,
                ],
                [
                    'key' => 'test2',
                    'value' => 'example2',
                    'multiplier' => 3,
                ],
            ],
        ]);

        $form = $this->factory->create(CreateEarningRuleFormType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function getMainData()
    {
        return [
            'name' => 'test',
            'description' => 'sth',
            'startAt' => '2016-08-01',
            'endAt' => '2016-10-10',
            'active' => false,
            'allTimeActive' => false,
        ];
    }

    /**
     * @param string $name
     *
     * @return array
     */
    protected function getDataForCustomEventLimit(string $name = 'year'): array
    {
        return [
            'earningRule' => [
                'type' => EarningRule::TYPE_CUSTOM_EVENT,
                'active' => true,
                'allTimeActive' => true,
                'description' => 'sth',
                'eventName' => $name,
                'target' => 'level',
                'levels' => [
                    'e82c96cf-32a3-43bd-9034-4df343e50000',
                ],
                'limit' => [
                    'active' => true,
                    'limit' => 10,
                    'period' => $name,
                ],
                'name' => $name,
                'pointsAmount' => 10,
                'pos' => [
                    '517c1372-d845-493c-ae8e-91b449ff13f8',
                ],
            ],
        ];
    }
}
