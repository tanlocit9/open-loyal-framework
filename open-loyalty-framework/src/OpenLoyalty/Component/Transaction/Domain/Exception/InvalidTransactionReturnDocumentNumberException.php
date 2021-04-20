<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Exception;

class InvalidTransactionReturnDocumentNumberException extends \Exception
{
    protected $message = 'transaction.not_found';
}
