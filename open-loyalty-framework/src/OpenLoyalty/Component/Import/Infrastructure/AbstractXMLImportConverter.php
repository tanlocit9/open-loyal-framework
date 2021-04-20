<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure;

use OpenLoyalty\Component\Import\Infrastructure\Validator\XmlNodeValidator;

/**
 * Class AbstractXMLImportConverter.
 */
abstract class AbstractXMLImportConverter implements XMLImportConverter
{
    /**
     * @param \SimpleXMLElement $element
     * @param array             $nodes
     *
     * @throws ImportConvertException
     */
    protected function checkValidNodes(\SimpleXMLElement $element, array $nodes)
    {
        $nodeValidator = new XmlNodeValidator();

        foreach ($nodes as $xpath => $requirements) {
            $result = $nodeValidator->validate($element, $xpath, $requirements);
            if ($result) {
                throw new ImportConvertException($result);
            }
        }
    }

    /**
     * @param string|null $value
     *
     * @return bool
     */
    protected function returnBool(?string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
