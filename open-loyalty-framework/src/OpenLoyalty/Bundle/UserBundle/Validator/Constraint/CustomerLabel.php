<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Class CustomerLabel.
 *
 * @Annotation
 */
class CustomerLabel extends Constraint
{
    public $message = 'Customer labels invalid data';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return CustomerLabelValidator::class;
    }
}
