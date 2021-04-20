<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Exception;

/**
 * Class AdminNotFoundException.
 */
class AdminNotFoundException extends \DomainException
{
    protected $message = 'An administrator not found';
}
