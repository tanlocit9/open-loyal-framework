<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Translation\Domain\Command;

use OpenLoyalty\Component\Translation\Domain\LanguageId;

/**
 * Class UpdateLevel.
 */
class LanguageCommand
{
    /**
     * @var LanguageId
     */
    private $languageId;

    /**
     * LanguageCommand constructor.
     *
     * @param LanguageId $languageId
     */
    public function __construct(LanguageId $languageId)
    {
        $this->languageId = $languageId;
    }

    /**
     * @return LanguageId
     */
    public function getLanguageId(): LanguageId
    {
        return $this->languageId;
    }
}
