<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use OpenLoyalty\Bundle\SettingsBundle\DependencyInjection\Compiler\ConfigureChoicesProviderCompilerPass;
use OpenLoyalty\Bundle\SettingsBundle\DependencyInjection\Compiler\LocaleListenerCompilerPass;
use OpenLoyalty\Bundle\SettingsBundle\DependencyInjection\Compiler\MarketingConfigCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class OpenLoyaltySettingsBundle.
 */
class OpenLoyaltySettingsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigureChoicesProviderCompilerPass());
        $container->addCompilerPass(new MarketingConfigCompilerPass());
        $container->addCompilerPass($this->buildMappingCompilerPass());
        $container->addCompilerPass(new LocaleListenerCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function buildMappingCompilerPass()
    {
        return DoctrineOrmMappingsPass::createYamlMappingDriver(
            [__DIR__.'/../../Component/Translation/Infrastructure/Persistence/Doctrine/ORM' => 'OpenLoyalty\Component\Translation\Domain'],
            [],
            false,
            ['OpenLoyaltySettings' => 'OpenLoyalty\Component\Translation\Domain']
        );
    }
}
