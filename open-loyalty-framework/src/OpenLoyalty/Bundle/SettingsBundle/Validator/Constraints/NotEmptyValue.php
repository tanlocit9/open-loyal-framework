<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class NotEmptyValue.
 *
 * @Annotation
 */
class NotEmptyValue extends Constraint
{
    const IS_BLANK_ERROR = 'de645tg6-e022-8g63-4353-acbcafc7fdc3';

    public $message = 'This value should not be blank.';
}
