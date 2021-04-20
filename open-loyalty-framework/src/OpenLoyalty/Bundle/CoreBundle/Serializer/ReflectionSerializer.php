<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Serializer;

use Broadway\Serializer\Serializer;
use Assert\Assertion as Assert;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use Broadway\Serializer\SerializationException;

/**
 * Class ReflectionSerializer.
 */
class ReflectionSerializer implements Serializer
{
    /**
     * {@inheritdoc}
     */
    public function serialize($object): array
    {
        return $this->serializeObjectRecursively($object);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function serializeValue($value)
    {
        if (is_object($value)) {
            return $this->serializeObjectRecursively($value);
        } elseif (is_array($value)) {
            return $this->serializeArrayRecursively($value);
        } else {
            return $value;
        }
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function serializeArrayRecursively(array $array): array
    {
        $data = [];
        foreach ($array as $key => $value) {
            $data[$key] = $this->serializeValue($value);
        }

        return $data;
    }

    /**
     * @param object $object
     *
     * @return array
     */
    private function serializeObjectRecursively($object): array
    {
        $reflection = new ReflectionObject($object);
        $properties = $reflection->getProperties();

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();

            $property->setAccessible(true);
            $value = $property->getValue($object);
            $property->setAccessible(false);

            $data[$name] = $this->serializeValue($value);
        }

        return [
            'class' => get_class($object),
            'payload' => $data,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(array $serializedObject)
    {
        return $this->deserializeObjectRecursively($serializedObject);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function deserializeValue($value)
    {
        if (is_array($value) && isset($value['class']) && isset($value['payload'])) {
            return $this->deserializeObjectRecursively($value);
        } elseif (is_array($value)) {
            return $this->deserializeArrayRecursively($value);
        } else {
            return $value;
        }
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function deserializeArrayRecursively(array $array): array
    {
        $data = [];
        foreach ($array as $key => $value) {
            $data[$key] = $this->deserializeValue($value);
        }

        return $data;
    }

    /**
     * @param $serializedObject
     *
     * @return \DateTime|object
     *
     * @throws \Assert\AssertionFailedException
     * @throws \ReflectionException
     */
    private function deserializeObjectRecursively($serializedObject)
    {
        Assert::keyExists($serializedObject, 'class', "Key 'class' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        if ($serializedObject['class'] === 'DateTime') {
            return new \DateTime(
                $serializedObject['payload']['date'],
                new \DateTimeZone($serializedObject['payload']['timezone'])
            );
        }

        $reflection = new ReflectionClass($serializedObject['class']);
        $properties = $reflection->getProperties();
        $object = $reflection->newInstanceWithoutConstructor();

        foreach ($serializedObject['payload'] as $name => $value) {
            $matchedProperty = $this->findProperty($properties, $name);
            if (null === $matchedProperty) {
                throw new SerializationException(sprintf(
                    'Property \'%s\' not found for object \'%s\'',
                    $name,
                    $serializedObject['class']
                ));
            }

            $value = $this->deserializeValue($value);

            $matchedProperty->setAccessible(true);
            $matchedProperty->setValue($object, $value);
            $matchedProperty->setAccessible(false);
        }

        return $object;
    }

    /**
     * @param ReflectionProperty[] $properties
     * @param string               $name
     *
     * @return null|ReflectionProperty
     */
    private function findProperty(array $properties, string $name)
    {
        foreach ($properties as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }

        return null;
    }
}
