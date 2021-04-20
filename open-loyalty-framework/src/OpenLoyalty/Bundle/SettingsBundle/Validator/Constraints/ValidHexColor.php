<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ValidHexColor.
 */
class ValidHexColor extends Constraint
{
    public $message = 'This is not a valid hex color.';
}
