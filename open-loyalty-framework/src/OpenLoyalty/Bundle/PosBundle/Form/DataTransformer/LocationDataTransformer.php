<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PosBundle\Form\DataTransformer;

use Assert\AssertionFailedException;
use OpenLoyalty\Component\Pos\Domain\Model\Location;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class LocationDataTransformer.
 */
class LocationDataTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException when the transformation fails
     */
    public function transform($value)
    {
        if (null == $value) {
            return;
        }

        if (!$value instanceof Location) {
            throw new InvalidArgumentException();
        }

        return $value->serialize();
    }

    /**
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException when the transformation fails
     */
    public function reverseTransform($value)
    {
        if ($value == null) {
            return;
        }

        try {
            return Location::deserialize($value);
        } catch (AssertionFailedException $e) {
            return new Location(
                isset($value['street']) ? $value['street'] : null,
                isset($value['address1']) ? $value['address1'] : null,
                isset($value['province']) ? $value['province'] : null,
                isset($value['city']) ? $value['city'] : null,
                isset($value['postal']) ? $value['postal'] : null,
                isset($value['country']) ? $value['country'] : null,
                isset($value['address2']) ? $value['address2'] : null,
                isset($value['lat']) ? $value['lat'] : null,
                isset($value['long']) ? $value['long'] : null,
                true
            );
        }
    }
}
