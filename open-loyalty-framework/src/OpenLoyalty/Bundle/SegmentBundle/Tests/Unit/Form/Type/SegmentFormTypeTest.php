<?php

namespace OpenLoyalty\Bundle\SegmentBundle\Tests\Unit\Form\Type;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\SegmentBundle\Form\Type\CriterionFormType;
use OpenLoyalty\Bundle\SegmentBundle\Form\Type\SegmentFormType;
use OpenLoyalty\Bundle\SegmentBundle\Form\Type\SegmentPartFormType;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class SegmentFormTypeTest.
 */
class SegmentFormTypeTest extends TypeTestCase
{
    private $validator;

    private $uuidGenerator;

    private $posRepository;

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

        $this->uuidGenerator = $this->getMockBuilder(UuidGeneratorInterface::class)->getMock();
        $this->uuidGenerator->method('generate')->willReturn('00000000-0000-0000-0000-0000000000'.rand(10, 99));

        $this->posRepository = $this->getMockBuilder(PosRepository::class)->getMock();
        $this->posRepository->method('findAll')->will($this->returnCallback(function () {
            $pos = [];
            $pos[] = new Pos(new PosId('00000000-0000-0000-0000-000000000000'));
            $pos[] = new Pos(new PosId('00000000-0000-0000-0000-000000000001'));

            return $pos;
        }));

        parent::setUp();
    }

    protected function getExtensions()
    {
        $type = new SegmentFormType();

        return array(
            new PreloadedExtension(array(
                $type,
                new SegmentPartFormType($this->uuidGenerator),
                new CriterionFormType($this->posRepository, $this->uuidGenerator),
            ), array()),
            new ValidatorExtension($this->validator),
        );
    }

    /**
     * @test
     */
    public function it_has_valid_data_when_creating_new_pos()
    {
        $formData = [
            'name' => 'test',
            'description' => 'desc',
            'parts' => [
                [
                    'criteria' => [
                        [
                            'type' => Criterion::TYPE_BOUGHT_IN_POS,
                            'posIds' => ['00000000-0000-0000-0000-000000000000', '00000000-0000-0000-0000-000000000001'],
                        ],
                        [
                            'type' => Criterion::TYPE_AVERAGE_TRANSACTION_AMOUNT,
                            'fromAmount' => 1,
                            'toAmount' => 10000,
                        ],
                        [
                            'type' => Criterion::TYPE_TRANSACTION_COUNT,
                            'min' => 10,
                            'max' => 20,
                        ],
                    ],
                ],
            ],
        ];

        $form = $this->factory->create(SegmentFormType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
