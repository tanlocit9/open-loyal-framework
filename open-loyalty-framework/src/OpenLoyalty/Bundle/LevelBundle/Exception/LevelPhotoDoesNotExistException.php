<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Exception;

/**
 * Class LevelPhotoDoesNotExistException.
 */
class LevelPhotoDoesNotExistException extends \Exception
{
    protected $message = 'Photo for level does not exist.';
}
