<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure\Validator;

use Ramsey\Uuid\Uuid;

/**
 * Class XmlNodeValidator.
 */
class XmlNodeValidator
{
    const DATE_CONVERT_FORMAT = 'Y-m-d';

    const DATE_TIME_FORMAT = 'datetime';
    const DATE_FORMAT = 'date';
    const DECIMAL_FORMAT = 'decimal';
    const INTEGER_FORMAT = 'integer';
    const VALID_CONST_FORMAT = 'valid_const';
    const BOOL_FORMAT = 'bool';
    const UUID_FORMAT = 'uuid';

    private $defaultRequirements = [
        'required' => false,
        'format' => null,
        'values' => [],
    ];

    /**
     * @param \SimpleXMLElement $element
     * @param string            $xpath
     * @param array             $requirements
     *
     * @return null|string
     */
    public function validate(\SimpleXMLElement $element, string $xpath, array $requirements = [])
    {
        $requirements = array_merge($this->defaultRequirements, $requirements);

        if ($requirements['required'] == true && empty($element->xpath($xpath))) {
            return sprintf('%s is required node', $xpath);
        }

        if ($requirements['format']) {
            $value = $element->xpath($xpath);
            $parsedValue = isset($value[0]) ? (string) $value[0] : '';

            switch ($requirements['format']) {
                case self::DATE_FORMAT:
                    $dt = \DateTime::createFromFormat(self::DATE_CONVERT_FORMAT, $parsedValue);
                    if (!$dt) {
                        return sprintf(
                            '%s has invalid date format (%s required)',
                            $xpath,
                            self::DATE_CONVERT_FORMAT
                        );
                    }
                    break;
                case self::DATE_TIME_FORMAT:
                    $dt = \DateTime::createFromFormat(DATE_ATOM, $parsedValue);
                    if (!$dt) {
                        return sprintf('%s has invalid date format (ATOM required)', $xpath);
                    }
                    break;
                case self::DECIMAL_FORMAT:
                    if (!is_numeric($parsedValue)) {
                        return sprintf('%s should be number value', $xpath);
                    }
                    break;
                case self::INTEGER_FORMAT:
                    if ((string) (int) $parsedValue != $parsedValue) {
                        return sprintf('%s should be integer value', $xpath);
                    }
                    break;
                case self::BOOL_FORMAT:
                    $requirements['values'] = ['true', 'false'];
                    // pass to valid const format
                    // no break
                case self::VALID_CONST_FORMAT:
                    if (!empty($parsedValue) && !in_array($parsedValue, $requirements['values'])) {
                        return sprintf(
                            '%s should one of (%s)',
                                $xpath,
                                implode(', ', $requirements['values'])
                        );
                    }
                    break;
                case self::UUID_FORMAT:
                    if (!empty($parsedValue) && !Uuid::isValid($parsedValue)) {
                        return sprintf('%s should be UUID', $xpath);
                    }
                    break;
            }
        }

        return;
    }
}
