<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Exception;

class CustomerNotFoundException extends \Exception
{
    protected $message = 'segment.parts.customer_not_found';
}
