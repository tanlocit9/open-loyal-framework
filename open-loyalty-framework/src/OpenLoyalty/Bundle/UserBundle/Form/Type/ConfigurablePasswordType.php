<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Form\Type;

use OpenLoyalty\Bundle\UserBundle\Validator\Constraint\PasswordRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ConfigurablePasswordType.
 */
class ConfigurablePasswordType extends AbstractType
{
    private const PASSWORD_VALIDATION_SIMPLE = 'simple';

    private const PASSWORD_VALIDATION_ADVANCED = 'advanced';

    /**
     * @var string
     */
    private $passwordValidationLevel;

    /**
     * ConfigurablePasswordType constructor.
     *
     * @param string $passwordValidationLevel
     */
    public function __construct(string $passwordValidationLevel)
    {
        $this->passwordValidationLevel = $passwordValidationLevel;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $options['constraints'] = [new NotBlank(), $this->buildPasswordRequirements($this->passwordValidationLevel)];
        $resolver->setDefaults($options);

        parent::configureOptions($resolver);
    }

    /**
     * @param string $passwordValidationLevel
     *
     * @return PasswordRequirements
     */
    public function buildPasswordRequirements($passwordValidationLevel): PasswordRequirements
    {
        switch ($passwordValidationLevel) {
            case self::PASSWORD_VALIDATION_ADVANCED:
                $requirements = [
                    'requireSpecialCharacter' => true,
                    'requireNumbers' => true,
                    'requireLetters' => true,
                    'requireCaseDiff' => true,
                    'minLength' => 8,
                ];
                break;
            case self::PASSWORD_VALIDATION_SIMPLE:
            default:
                $requirements = [
                    'requireLetters' => false,
                    'minLength' => 8,
                ];
                break;
        }

        $requirementsObj = new PasswordRequirements($requirements);

        return $requirementsObj;
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return PasswordType::class;
    }
}
