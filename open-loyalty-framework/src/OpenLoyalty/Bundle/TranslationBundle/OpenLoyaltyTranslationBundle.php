<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle;

use OpenLoyalty\Bundle\TranslationBundle\DependencyInjection\Compiler\DoctrineORMInfoCompilerPass;
use OpenLoyalty\Bundle\TranslationBundle\DependencyInjection\Compiler\KnpDefaultLocaleCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class OpenLoyaltyTransactionBundle.
 */
class OpenLoyaltyTranslationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrineORMInfoCompilerPass());
        $container->addCompilerPass(new KnpDefaultLocaleCompilerPass());
    }
}
