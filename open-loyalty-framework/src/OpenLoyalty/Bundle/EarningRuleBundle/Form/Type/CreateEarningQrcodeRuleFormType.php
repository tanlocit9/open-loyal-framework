<?php
/**
 * Copyright ÂŠ 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningQrcodeRule;
use Symfony\Component\Validator\Constraints\Uuid;

/**
 * Class CreateEarningQrcodeRuleFormType.
 */
class CreateEarningQrcodeRuleFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank()],
            ]);
        $builder
            ->add('earningRuleId', TextType::class, [
                'required' => false,
                'constraints' => [new Uuid()],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EarningQrcodeRule::class,
        ));
    }
}
