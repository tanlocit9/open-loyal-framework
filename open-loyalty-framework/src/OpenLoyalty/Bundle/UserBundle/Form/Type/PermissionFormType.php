<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Form\Type;

use OpenLoyalty\Bundle\UserBundle\Model\AclAvailableObject;
use OpenLoyalty\Bundle\UserBundle\Service\AclManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class PermissionFormType.
 */
class PermissionFormType extends AbstractType
{
    /**
     * @var AclManagerInterface
     */
    private $aclManager;

    /**
     * PermissionFormType constructor.
     *
     * @param AclManagerInterface $aclManager
     */
    public function __construct(AclManagerInterface $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $accesses = array_map(function (AclAvailableObject $object) {
            return $object->getCode();
        }, $this->aclManager->getAvailableAccesses());

        $resources = array_map(function (AclAvailableObject $object) {
            return $object->getCode();
        }, $this->aclManager->getAvailableResources());

        $builder->add('resource', ChoiceType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
            'choices' => $resources,
        ]);

        $builder->add('access', ChoiceType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
            'choices' => $accesses,
        ]);
    }
}
