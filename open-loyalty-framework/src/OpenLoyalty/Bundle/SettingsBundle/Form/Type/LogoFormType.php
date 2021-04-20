<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\Type;

use OpenLoyalty\Bundle\SettingsBundle\Model\Logo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LogoFormType.
 */
class LogoFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $allowedMaxSize = '2M';

    /**
     * @var array
     */
    protected $allowedMimeTypes = [
        'image/png',
        'image/jpg',
        'image/jpeg',
    ];

    /**
     * @var int
     */
    protected $allowedMinWidth = 200;

    /**
     * @var int
     */
    protected $allowedMaxWidth = 2560;

    /**
     * @var int
     */
    protected $allowedMinHeight = 200;

    /**
     * @var int
     */
    protected $allowedMaxHeight = 1440;

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
                new Assert\Image([
                    'minWidth' => $options['allowedMinWidth'],
                    'maxWidth' => $options['allowedMaxWidth'],
                    'minHeight' => $options['allowedMinHeight'],
                    'maxHeight' => $options['allowedMaxHeight'],
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
            'data_class' => Logo::class,
            'allowedMaxSize' => $this->allowedMaxSize,
            'allowedMimeTypes' => $this->allowedMimeTypes,
            'allowedMinWidth' => $this->allowedMinWidth,
            'allowedMaxWidth' => $this->allowedMaxWidth,
            'allowedMinHeight' => $this->allowedMinHeight,
            'allowedMaxHeight' => $this->allowedMaxHeight,
        ]);
    }
}
