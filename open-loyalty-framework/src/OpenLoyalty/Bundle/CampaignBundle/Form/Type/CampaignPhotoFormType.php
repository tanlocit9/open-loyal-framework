<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use OpenLoyalty\Bundle\CampaignBundle\Validator\Constraints;

/**
 * Class CampaignPhotoFormType.
 */
class CampaignPhotoFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'file',
            FileType::class,
            [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Constraints\Image(
                        [
                            'mimeTypes' => ['image/png', 'image/gif', 'image/jpeg', 'image/jpg'],
                            'maxSize' => '2M',
                        ]
                    ),
                ],
            ]
        );
    }
}
