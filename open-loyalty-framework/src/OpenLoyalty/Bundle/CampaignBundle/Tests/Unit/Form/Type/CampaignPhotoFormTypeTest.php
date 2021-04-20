<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\CampaignBundle\Form\Type\CampaignCollectionPhotoFormType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CampaignPhotoFormTypeTest.
 */
final class CampaignPhotoFormTypeTest extends TypeTestCase
{
    /**
     * @var ValidatorInterface | MockObject
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('addConstraint')->willReturn(true);
        $metadata->method('addPropertyConstraint')->willReturn(true);

        $this->validator->method('getMetadataFor')->willReturn($metadata);

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions(): array
    {
        $type = new CampaignCollectionPhotoFormType();

        return [
            new PreloadedExtension([$type], []),
            new ValidatorExtension($this->validator),
        ];
    }

    /**
     * @test
     */
    public function it_submit_multiple_files(): void
    {
        $formData = [
            'file' => [
                'file1',
                'file2',
            ],
        ];

        $form = $this->factory->create(CampaignCollectionPhotoFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        $this->assertArrayHasKey('photos', $children);
    }
}
