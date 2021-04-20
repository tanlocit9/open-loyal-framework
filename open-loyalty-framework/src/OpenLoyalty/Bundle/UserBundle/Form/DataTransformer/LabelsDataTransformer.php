<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\DataTransformer;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class LabelsDataTransformer.
 */
class LabelsDataTransformer implements DataTransformerInterface
{
    const ENTRIES_DELIMITER = ';';
    const KEY_VALUE_DELIMITER = ':';

    /**
     * {@inheritdoc}
     */
    public function transform($value): ?string
    {
        if ($value == null) {
            return null;
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException();
        }

        $values = array_map(function (Label $label): string {
            return $label->getKey().self::KEY_VALUE_DELIMITER.$label->getValue();
        }, $value);

        return implode(self::ENTRIES_DELIMITER, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($values): array
    {
        if ($values !== null && !is_string($values)) {
            throw new \InvalidArgumentException();
        }

        $values = explode(self::ENTRIES_DELIMITER, $values);

        $transformed = array_map(function ($code) {
            if (!$code || !is_string($code) || strpos($code, self::KEY_VALUE_DELIMITER) === false) {
                return null;
            }

            [$key, $value] = explode(self::KEY_VALUE_DELIMITER, $code, 2);

            return new Label($key, $value);
        }, $values);

        return array_filter($transformed);
    }
}
