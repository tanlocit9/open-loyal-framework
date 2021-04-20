<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Infrastructure\Serializer;

use Broadway\Serializer\SerializationException;
use Broadway\Serializer\Serializer;
use Broadway\Serializer\Serializable;
use Assert\Assertion as Assert;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;

/**
 * Class SimpleInterfaceSerializer.
 */
final class SimpleInterfaceSerializer implements Serializer
{
    /**
     * {@inheritdoc}
     */
    public function serialize($object): array
    {
        if (!$object instanceof Serializable) {
            throw new SerializationException(sprintf(
                'Object \'%s\' does not implement Broadway\Serializer\Serializable',
                get_class($object)
            ));
        }

        $data = [
            'class' => get_class($object),
            'payload' => $object->serialize(),
        ];

        if ($object instanceof VersionableReadModel) {
            $data['version'] = $object->getVersion();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(array $serializedObject)
    {
        Assert::keyExists($serializedObject, 'class', "Key 'class' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        if (!in_array(Serializable::class, class_implements($serializedObject['class']))) {
            throw new SerializationException(
                sprintf(
                    'Class \'%s\' does not implement Broadway\Serializer\Serializable',
                    $serializedObject['class']
                )
            );
        }

        $object = $serializedObject['class']::deserialize($serializedObject['payload']);

        if (array_key_exists('version', $serializedObject) && $object instanceof VersionableReadModel) {
            $object->setVersion($serializedObject['version']);
        }

        return $object;
    }
}
