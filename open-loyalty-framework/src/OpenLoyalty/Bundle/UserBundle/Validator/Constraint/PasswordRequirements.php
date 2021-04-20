<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Class PasswordRequirements.
 *
 * @Annotation
 */
class PasswordRequirements extends Constraint
{
    public $tooShortMessage = 'Your password must be at least {{length}} characters long.';
    public $missingLettersMessage = 'Your password must include at least one letter.';
    public $requireCaseDiffMessage = 'Your password must include both upper and lower case letters.';
    public $missingNumbersMessage = 'Your password must include at least one number.';
    public $missingSpecialCharacterMessage = 'Your password must contain at least one special character.';
    public $minLength = 6;
    public $requireLetters = true;
    public $requireCaseDiff = false;
    public $requireNumbers = false;
    public $requireSpecialCharacter = false;
}
