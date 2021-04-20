<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\EarningRuleBundle\Form\Type\EditEarningRuleFormType;
use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableProvider;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class EditEarningRuleFormTypeTest.
 */
class EditEarningRuleFormTypeTest extends TypeTestCase
{
    private $validator;

    protected function setUp()
    {
        $this->validator = $this->getMockBuilder(
            'Symfony\Component\Validator\Validator\ValidatorInterface'
        )->getMock();
        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));
        $metadata = $this->getMockBuilder('Symfony\Component\Validator\Mapping\ClassMetadata')
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
        $translaction = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $type = new EditEarningRuleFormType(
            new StoppableProvider(),
            $translaction
        );

        return array(
            new PreloadedExtension(array($type), array()),
            new ValidatorExtension($this->validator),
        );
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function it_throws_exception_when_type_not_provided()
    {
        $this->factory->create(EditEarningRuleFormType::class);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function it_throws_exception_when_wrong_type_provided()
    {
        $this->factory->create(EditEarningRuleFormType::class, null, ['type' => 'test']);
    }

    /**
     * @test
     */
    public function it_has_valid_data()
    {
        $formData = array_merge($this->getMainData(), [
            'skuIds' => ['123'],
            'pointsAmount' => 100,
        ]);

        $form = $this->factory->create(EditEarningRuleFormType::class, null, ['type' => EarningRule::TYPE_PRODUCT_PURCHASE]);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

        $data = $form->getData();
        $this->assertInstanceOf(EarningRule::class, $data);
    }

    /**
     * @test
     */
    public function it_has_valid_multiply_data()
    {
        $formData = array_merge($this->getMainData(), [
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

        $form = $this->factory->create(EditEarningRuleFormType::class, null, ['type' => EarningRule::TYPE_MULTIPLY_BY_PRODUCT_LABELS]);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

        $data = $form->getData();
        $this->assertInstanceOf(EarningRule::class, $data);
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
}
