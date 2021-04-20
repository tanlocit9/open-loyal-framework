<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\DataTransformer;

use DateTime;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class DateTransformer.
 */
class DateTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value === null || !is_string($value) || $value === '') {
            return null;
        }

        try {
            $dateTime = DateTime::createFromFormat('Y-m-d', $value);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$dateTime instanceof DateTime) {
            return null;
        }

        return $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!$value instanceof DateTime) {
            return null;
        }

        return $value->format('Y-m-d');
    }
}
