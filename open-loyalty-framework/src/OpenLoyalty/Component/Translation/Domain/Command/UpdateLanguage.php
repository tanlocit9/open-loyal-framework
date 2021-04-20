<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Translation\Domain\Command;

use OpenLoyalty\Component\Translation\Domain\LanguageId;

/**
 * Class UpdateLanguage.
 */
class UpdateLanguage extends LanguageCommand
{
    /**
     * @var array
     */
    protected $languageData;

    /**
     * UpdateLanguage constructor.
     *
     * @param LanguageId $levelId
     * @param array      $languageData
     */
    public function __construct(LanguageId $levelId, array $languageData)
    {
        parent::__construct($levelId);
        $this->languageData = $languageData;
    }

    /**
     * @return array
     */
    public function getLanguageData(): array
    {
        return $this->languageData;
    }
}
