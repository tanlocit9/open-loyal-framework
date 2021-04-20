<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use OpenLoyalty\Bundle\ActivationCodeBundle\DependencyInjection\CompilerPass\InjectSmsGatewayCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class OpenLoyaltyActivationCodeBundle.
 */
class OpenLoyaltyActivationCodeBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass($this->buildMappingCompilerPass());
        $container->addCompilerPass(new InjectSmsGatewayCompilerPass());
    }

    /**
     * @return DoctrineOrmMappingsPass
     */
    public function buildMappingCompilerPass()
    {
        return DoctrineOrmMappingsPass::createYamlMappingDriver(
            [__DIR__.'/../../Component/ActivationCode/Infrastructure/Persistence/Doctrine/ORM' => 'OpenLoyalty\Component\ActivationCode\Domain'],
            [],
            false,
            ['OpenLoyaltyActivationCode' => 'OpenLoyalty\Component\ActivationCode\Domain']
        );
    }
}
