<?php

declare(strict_types=1);

/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\EarningRuleBundle\Form\Type\CreateEarningGeoRuleFormType;
use OpenLoyalty\Bundle\EarningRuleBundle\Form\Type\CreateEarningRuleFormType;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableProvider;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class CreateEarningGeoRuleFormTypeTest.
 */
class CreateEarningGeoRuleFormTypeTest extends TypeTestCase
{
    private $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();
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

    /**
     * @return array
     */
    protected function getExtensions(): array
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $type = new CreateEarningRuleFormType(
            new StoppableProvider(),
            $translator
        );

        return [
            new PreloadedExtension([$type], []),
            new ValidatorExtension($this->validator),
        ];
    }

    /**
     * @test
     */
    public function it_has_valid_values_with_decimal_point_when_creating_geo_rule_event(): void
    {
        $formData = [
            'latitude' => 1.022353,
            'longitude' => -1.022354,
        ];
        $form = $this->factory->create(CreateEarningGeoRuleFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $data = $form->getData();

        $this->assertEquals(1.02235, $data->getLatitude(), 'Result should be 1.02235');
        $this->assertEquals(-1.02235, $data->getLongitude(), 'Result should be -1.02235');
    }
}
