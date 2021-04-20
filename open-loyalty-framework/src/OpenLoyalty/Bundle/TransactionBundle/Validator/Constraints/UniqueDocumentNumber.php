<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueDocumentNumber.
 *
 * @Annotation
 */
class UniqueDocumentNumber extends Constraint
{
    public $message = 'transaction.document_number_should_be_unique';
}
