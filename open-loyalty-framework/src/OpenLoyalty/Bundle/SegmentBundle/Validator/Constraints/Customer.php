<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Customer.
 *
 * @Annotation
 */
class Customer extends Constraint
{
    public $message = 'segment.parts.not_found';
}
