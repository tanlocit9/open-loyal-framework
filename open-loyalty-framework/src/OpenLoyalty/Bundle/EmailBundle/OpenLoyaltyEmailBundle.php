<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EmailBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class OpenLoyaltyEmailBundle.
 */
class OpenLoyaltyEmailBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass($this->buildMappingCompilerPass());
    }

    /**
     * @return DoctrineOrmMappingsPass
     */
    public function buildMappingCompilerPass()
    {
        return DoctrineOrmMappingsPass::createYamlMappingDriver(
            [__DIR__.'/../../Component/Email/Infrastructure/Persistence/Doctrine/ORM' => 'OpenLoyalty\Component\Email\Domain'],
            [],
            false,
            ['OpenLoyaltyEmail' => 'OpenLoyalty\Component\Email\Domain']
        );
    }
}
