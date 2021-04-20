<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\Type;

use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Service\AclManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Type as Numeric;

/**
 * Class AdminSelfEditFormType.
 */
class AdminSelfEditFormType extends AbstractType
{
    /**
     * @var AclManagerInterface
     */
    private $aclManager;

    /**
     * AdminFormType constructor.
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', TextType::class, [
            'required' => false,
        ]);
        $builder->add('lastName', TextType::class, [
            'required' => false,
        ]);
        $builder->add('phone', TextType::class, [
            'required' => false,
            'constraints' => [
                new Numeric(['type' => 'numeric', 'message' => 'Incorrect phone number format, use +00000000000']),
            ],
        ]);

        $rolesChoices = array_map(
            function (Role $role) {
                return (string) $role->getId();
            },
            $this->aclManager->getAdminRoles()
        );

        $builder->add('roles', CollectionType::class, [
            'entry_type' => ChoiceType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'constraints' => [
                new Count(['max' => 1, 'min' => 1]),
            ],
            'entry_options' => [
                'choices' => array_combine($rolesChoices, $rolesChoices),
            ],
        ]);

        $builder->add('email', TextType::class, [
            'required' => true,
        ]);
    }
}
