<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;

/**
 * Trait SortFilter.
 */
trait SortFilter
{
    /**
     * Whitelist of field names that can be used for sorting.
     *
     * @param string $sortField
     *
     * @return string
     */
    public function validateSort(string $sortField): string
    {
        $entityManager = $this->getEntityManager();
        $metadata = $entityManager->getClassMetadata($this->getClassName());
        $fieldNames = $metadata->getFieldNames();

        if (!in_array($sortField, $fieldNames)) {
            return $fieldNames[0];
        }

        return $sortField;
    }

    /**
     * @return EntityManager
     */
    abstract protected function getEntityManager();

    /**
     * @return string
     */
    abstract public function getClassName();
}
