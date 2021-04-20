<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Exception;

/**
 * Class TooManyCustomersFoundException.
 */
class TooManyCustomersFoundException extends \Exception
{
    protected $message = 'segment.parts.too_many_customers_found';
}
