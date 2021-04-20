<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\DependencyInjection;

use OpenLoyalty\Bundle\ActivationCodeBundle\Service\DummySmsApi;
use OpenLoyalty\Bundle\SmsApiBundle\SmsApi\OloySmsApi;
use OpenLoyalty\Bundle\WorldTextBundle\Service\WorldTextSender;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('open_loyalty_activation_code');
        $rootNode->children()
            ->enumNode('sms_gateway')->values(
                [
                    OloySmsApi::GATEWAY_CODE,
                    WorldTextSender::GATEWAY_CODE,
                    DummySmsApi::GATEWAY_CODE,
                ]
            )->end();
        $rootNode->children()
            ->enumNode('code_type')->values(
                [
                    'alphanum',
                    'num',
                ]
            )->defaultValue('num')->end();

        return $treeBuilder;
    }
}
