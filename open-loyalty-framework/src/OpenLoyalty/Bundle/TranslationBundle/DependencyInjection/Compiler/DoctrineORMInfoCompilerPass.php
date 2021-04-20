<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle\DependencyInjection\Compiler;

use OpenLoyalty\Bundle\TranslationBundle\ObjectInfo\DoctrineORMInfo;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DoctrineORMInfoCompilerPass.
 */
class DoctrineORMInfoCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('a2lix_auto_form.object_info.doctrine_orm_info');
        $definition->setClass(DoctrineORMInfo::class);
    }
}
