<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure;

/**
 * Class XMLImportConverter.
 */
interface XMLImportConverter
{
    /**
     * @param \SimpleXMLElement $element
     *
     * @throws ImportConvertException
     *
     * @return mixed
     */
    public function convert(\SimpleXMLElement $element);

    /**
     * @param \SimpleXMLElement $element
     *
     * @return string
     */
    public function getIdentifier(\SimpleXMLElement $element): string;
}
