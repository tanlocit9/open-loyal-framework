<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class TransactionReturnDocument.
 *
 * @Annotation
 */
class TransactionReturnDocument extends Constraint
{
    /**
     * @var bool
     */
    protected $isManually;

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return array_merge(parent::getRequiredOptions(), ['isManually']);
    }

    /**
     * @return bool
     */
    public function getIsManually(): bool
    {
        return $this->isManually;
    }
}
