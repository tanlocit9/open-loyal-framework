<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\DependencyInjection\CompilerPass;

use OpenLoyalty\Bundle\ActivationCodeBundle\Generator\AlphaNumericCodeGenerator;
use OpenLoyalty\Bundle\ActivationCodeBundle\Generator\NumericCodeGenerator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class InjectCodeGeneratorCompilerPass.
 */
class InjectCodeGeneratorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('oloy.activation_code_manager')) {
            return;
        }

        if (!$container->hasParameter('oloy.activation_code.code_type')) {
            return;
        }

        $codeType = $container->getParameter('oloy.activation_code.code_type');

        switch ($codeType) {
            case AlphaNumericCodeGenerator::TYPE:
                $service = 'oloy.activation_code.alpha_num';
                break;
            case NumericCodeGenerator::TYPE:
                $service = 'oloy.activation_code.num';
                break;
            default:
                return;
        }

        if (!$service) {
            throw new InvalidConfigurationException(sprintf('Code type (%s) does not exist', $codeType));
        }

        $manager = $container->getDefinition('oloy.activation_code_manager');
        $manager->addMethodCall('setCodeGenerator', [new Reference($service)]);
    }
}
