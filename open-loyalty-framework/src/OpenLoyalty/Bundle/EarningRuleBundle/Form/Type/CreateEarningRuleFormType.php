<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Form\Type;

use OpenLoyalty\Bundle\EarningRuleBundle\Form\DataTransformer\PosDataTransformer;
use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRule;
use OpenLoyalty\Bundle\EarningRuleBundle\Form\DataTransformer\LevelsDataTransformer;
use OpenLoyalty\Bundle\EarningRuleBundle\Form\DataTransformer\SegmentsDataTransformer;
use OpenLoyalty\Bundle\EarningRuleBundle\Validator\Constraints\GeoType;
use OpenLoyalty\Component\EarningRule\Domain\PointsEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\InstantRewardRule;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableProvider;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type as CType;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CreateEarningRuleFormType.
 */
class CreateEarningRuleFormType extends BaseEarningRuleFormType
{
    /**
     * @var StoppableProvider
     */
    private $stoppableProvider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CreateEarningRuleFormType constructor.
     *
     * @param StoppableProvider   $stoppableProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(StoppableProvider $stoppableProvider, TranslatorInterface $translator)
    {
        $this->stoppableProvider = $stoppableProvider;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'required' => true,
            'constraints' => [new NotBlank()],
            'choices' => [
                'General spending rule' => EarningRule::TYPE_POINTS,
                'Event rule' => EarningRule::TYPE_EVENT,
                'Custom event rule' => EarningRule::TYPE_CUSTOM_EVENT,
                'Product purchase' => EarningRule::TYPE_PRODUCT_PURCHASE,
                'Multiply earned points' => EarningRule::TYPE_MULTIPLY_FOR_PRODUCT,
                'Multiply earned points by labels' => EarningRule::TYPE_MULTIPLY_BY_PRODUCT_LABELS,
                'Referral' => EarningRule::TYPE_REFERRAL,
                'Instant Reward' => InstantRewardRule::TYPE_INSTANT_REWARD,
                'Geolocation' => EarningRule::TYPE_GEOLOCATION,
                'QRcode' => EarningRule::TYPE_QRCODE,
            ],
        ]);

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextareaType::class, ['required' => true,
              'constraints' => [new NotBlank()],
            ])
            ->add('target', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'level' => 'level',
                    'segment' => 'segment',
                ],
                'mapped' => false,
            ]);
        $builder->add(
                $builder->create('levels', CollectionType::class, [
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'error_bubbling' => false,
                    'constraints' => [
                        new Callback([$this, 'validateTarget']),
                    ],
                ])->addModelTransformer(new LevelsDataTransformer())
            );
        $builder->add(
            $builder->create('segments', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'constraints' => [
                    new Callback([$this, 'validateTarget']),
                ],
            ])->addModelTransformer(new SegmentsDataTransformer())
            );
        $builder->add(
            $builder->create('pos', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
            ])
            ->addModelTransformer(new PosDataTransformer())
        );
        $builder->add('active', CheckboxType::class, ['required' => false]);
        $builder->add('allTimeActive', CheckboxType::class, ['required' => false]);
        $builder->add('startAt', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',
                'format' => DateTimeType::HTML5_FORMAT,
            ]);
        $builder->add('endAt', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',
                'format' => DateTimeType::HTML5_FORMAT,
            ]);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'configureFieldsBasedOnType']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureFieldsBasedOnType(FormEvent $event)
    {
        $data = $event->getData();

        $form = $event->getForm();
        if (!isset($data['type'])) {
            $form->get('type')->addError(new FormError((new NotBlank())->message));

            return;
        }
        $type = $data['type'];
        if ($type == EarningRule::TYPE_QRCODE) {
            $form
                ->add('code', TextType::class, [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ])
                ->add('pointsAmount', NumberType::class, [
                    'scale' => 2,
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ])
                ->add('limit', EarningRuleLimitFormType::class);
        } elseif ($type == EarningRule::TYPE_GEOLOCATION) {
            $form
                ->add('latitude', TextType::class, [
                    'required' => true,
                    'constraints' => [
                        new GeoType(),
                    ],
                ])
                ->add('longitude', TextType::class, [
                    'required' => true,
                    'constraints' => [
                        new GeoType(),
                    ],
                ])
                ->add('radius', NumberType::class, [
                    'required' => true,
                    'constraints' => [
                        new NotNull(['message' => $this->translator->trans('earning_rule.geo_rule.constraints_radius')]),
                        new CType([
                            'type' => 'numeric',
                        ]),
                        new Range(['min' => 1]),
                    ],
                ])
                ->add('pointsAmount', NumberType::class, [
                    'scale' => 2,
                    'required' => true,
                    'constraints' => [
                        new NotNull(['message' => $this->translator->trans('earning_rule.geo_rule.constraints_points')]),
                        new Range(['min' => 1]),
                    ],
                ])
                ->add('limit', EarningRuleLimitFormType::class);
        } elseif ($type == EarningRule::TYPE_POINTS) {
            $form
                ->add('pointValue', NumberType::class, [
                    'scale' => 2,
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ])
                ->add('excludedSKUs', ExcludedSKUsFormType::class)
                ->add('labelsInclusionType', ChoiceType::class, [
                    'choices' => [
                        PointsEarningRule::LABELS_INCLUSION_TYPE_NONE,
                        PointsEarningRule::LABELS_INCLUSION_TYPE_EXCLUDE,
                        PointsEarningRule::LABELS_INCLUSION_TYPE_INCLUDE,
                    ],
                    'required' => false,
                    'empty_data' => PointsEarningRule::LABELS_INCLUSION_TYPE_NONE,
                ])
                ->add('excludedLabels', ExcludedLabelsFormType::class)
                ->add('includedLabels', IncludedLabelsFormType::class)
                ->add('excludeDeliveryCost', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('minOrderValue', NumberType::class);
        } elseif ($type == EarningRule::TYPE_EVENT) {
            $form
                ->add('eventName', TextType::class, [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ])
                ->add('pointsAmount', NumberType::class, [
                    'scale' => 2,
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ]);
        } elseif ($type == EarningRule::TYPE_CUSTOM_EVENT) {
            $form
                ->add('eventName', TextType::class, [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ])
                ->add('pointsAmount', NumberType::class, [
                    'scale' => 2,
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ])
                ->add('limit', EarningRuleLimitFormType::class);
        } elseif ($type == EarningRule::TYPE_REFERRAL) {
            $form
                ->add('eventName', TextType::class, [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ])
                ->add('rewardType', TextType::class, [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ])
                ->add('pointsAmount', NumberType::class, [
                    'scale' => 2,
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ]);
        } elseif ($type == EarningRule::TYPE_PRODUCT_PURCHASE) {
            $form
                ->add('skuIds', CollectionType::class, [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'entry_type' => TextType::class,
                    'error_bubbling' => false,
                    'constraints' => [new Count(['min' => 1])],
                ])
                ->add('pointsAmount', NumberType::class, [
                    'scale' => 2,
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ]);
        } elseif ($type == EarningRule::TYPE_MULTIPLY_FOR_PRODUCT) {
            $form
                ->add('skuIds', CollectionType::class, [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'entry_type' => TextType::class,
                ])
                ->add('multiplier', NumberType::class, [
                    'required' => true,
                    'scale' => 2,
                    'constraints' => [new NotBlank()],
                ])
                ->add('labels', LabelsFormType::class);
        } elseif ($type == EarningRule::TYPE_MULTIPLY_BY_PRODUCT_LABELS) {
            $form
                ->add('labelMultipliers', CollectionType::class, [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'entry_type' => LabelMultipliersFormType::class,
                    'error_bubbling' => false,
                    'constraints' => [new Count(['min' => 1])],
                ]);
        } elseif ($type === EarningRule::TYPE_INSTANT_REWARD) {
            $form
                ->add('rewardCampaignId', CampaignIdFormType::class, [
                    'required' => true,
                    'constraints' => [new NotNull()],
                ]);
        }
        if (!isset($data['target'])) {
            return;
        }
        if ($this->stoppableProvider->isStoppableByType($type)) {
            $form->add('lastExecutedRule', CheckboxType::class, ['required' => false]);
        }
        $target = $data['target'];
        if ($target == 'level') {
            $data['segments'] = [];
        } elseif ($target == 'segment') {
            $data['levels'] = [];
        }
        $event->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EarningRule::class,
        ]);
    }
}
