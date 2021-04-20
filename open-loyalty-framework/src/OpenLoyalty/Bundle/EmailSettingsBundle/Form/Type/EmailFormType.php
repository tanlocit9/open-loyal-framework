<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\EmailSettingsBundle\Form\Type;

use OpenLoyalty\Bundle\EmailSettingsBundle\Form\Event\RewardRedeemedEmailToFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Class EmailFormType.
 */
class EmailFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            $builder->create(
                'key',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Length(['max' => 255]),
                    ],
                ]
            )
        );

        $builder->add(
            $builder->create(
                'subject',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Length(['max' => 255]),
                    ],
                ]
            )
        );

        $builder->add(
            $builder->create(
                'content',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank()],
                ]
            )
        );

        $builder->add(
            $builder->create(
                'sender_name',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank()],
                ]
            )
        );

        $builder->add(
            $builder->create(
                'sender_email',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank(), new Email()],
                ]
            )
        );

        $builder->add(
            $builder->create(
                'receiver_email',
                TextType::class
            )
        );

        $builder->addEventSubscriber(new RewardRedeemedEmailToFieldSubscriber());
    }
}
