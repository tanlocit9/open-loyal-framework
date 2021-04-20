<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\Type;

use OpenLoyalty\Bundle\SettingsBundle\Model\Conditions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LogoFormType.
 */
class ConditionsFileType extends AbstractType
{
    /**
     * @var string
     */
    protected $allowedMaxSize = '10M';

    /**
     * @var array
     */
    protected $allowedMimeTypes = [
        'application/pdf',
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, [
            'required' => true,
            'constraints' => [
                new Assert\File([
                    'maxSize' => $options['allowedMaxSize'],
                    'mimeTypes' => $options['allowedMimeTypes'],
                ]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Conditions::class,
            'allowedMaxSize' => $this->allowedMaxSize,
            'allowedMimeTypes' => $this->allowedMimeTypes,
        ]);
    }
}
