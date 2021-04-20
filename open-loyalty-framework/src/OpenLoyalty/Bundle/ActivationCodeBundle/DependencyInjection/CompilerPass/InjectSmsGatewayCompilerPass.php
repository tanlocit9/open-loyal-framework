<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\DependencyInjection\CompilerPass;

use OpenLoyalty\Bundle\ActivationCodeBundle\Provider\AvailableAccountActivationMethodsChoices;
use OpenLoyalty\Bundle\ActivationCodeBundle\Provider\SmsGatewayConfigFields;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\DummySmsApi;
use OpenLoyalty\Bundle\SmsApiBundle\SmsApi\OloySmsApi;
use OpenLoyalty\Bundle\WorldTextBundle\Service\WorldTextSender;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class InjectSmsGatewayCompilerPass.
 */
class InjectSmsGatewayCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('oloy.activation_code_manager')) {
            return;
        }

        if (!$container->hasParameter('oloy.activation_code.sms_gateway')) {
            return;
        }

        $smsGateway = $container->getParameter('oloy.activation_code.sms_gateway');

        switch ($smsGateway) {
            case DummySmsApi::GATEWAY_CODE:
                $service = 'open_loyalty.sms.dummy';
                break;
            case WorldTextSender::GATEWAY_CODE:
                $service = 'open_loyalty_world_text.sender';
                break;
            case OloySmsApi::GATEWAY_CODE:
                $service = 'oloy.sms_api';
                break;
            default:
                $service = null;
        }

        if (!$service) {
            throw new InvalidConfigurationException(sprintf('Sms gateway (%s) does not exist', $smsGateway));
        }

        $manager = $container->findDefinition('oloy.activation_code_manager');
        $manager->addMethodCall('setSmsSender', [new Reference($service)]);
        $container->setAlias('oloy.activation.sms_gateway', $service);

        $manager = $container->findDefinition('ol.settings.form_type.settings');
        $manager->addMethodCall('setSmsSender', [new Reference('oloy.activation.sms_gateway')]);

        $manager = $container->getDefinition(SmsGatewayConfigFields::class);
        $manager->setArgument('$smsGateway', new Reference($service));

        $manager = $container->getDefinition(AvailableAccountActivationMethodsChoices::class);
        $manager->setArgument('$smsGateway', new Reference($service));
    }
}
