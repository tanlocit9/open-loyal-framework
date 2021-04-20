<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle\ObjectInfo;

use A2lix\AutoFormBundle\ObjectInfo\DoctrineORMInfo as BaseDoctrineORMInfo;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;

/**
 * Class DoctrineORMInfo.
 */
class DoctrineORMInfo extends BaseDoctrineORMInfo
{
    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * DoctrineORMInfo constructor.
     *
     * @param ClassMetadataFactory $classMetadataFactory
     */
    public function __construct(ClassMetadataFactory $classMetadataFactory)
    {
        parent::__construct($classMetadataFactory);
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * Overrides behaviour default class in order to using extending doctrine class as domain class.
     *
     * {@inheritdoc}
     */
    public function getAssociationTargetClass(string $class, string $fieldName): string
    {
        if ($this->classMetadataFactory->hasMetadataFor($class)) {
            $metadata = $this->classMetadataFactory->getMetadataFor($class);
        } else {
            $parentClass = get_parent_class($class);

            if (!$parentClass) {
                throw new \RuntimeException(sprintf('Unable to find mapping class (looked at parent class of %s)', $class));
            }

            $metadata = $this->classMetadataFactory->getMetadataFor($parentClass);
        }

        if (!$metadata->hasAssociation($fieldName)) {
            throw new \RuntimeException(sprintf('Unable to find the association target class of "%s" in %s.', $fieldName, $class));
        }

        return $metadata->getAssociationTargetClass($fieldName);
    }
}
