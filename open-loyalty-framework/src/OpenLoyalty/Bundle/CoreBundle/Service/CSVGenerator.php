<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\Service;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Bundle\CoreBundle\CSVGenerator\MapperInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class CSVGenerator.
 */
class CSVGenerator implements GeneratorInterface
{
    /** @var PropertyAccessor */
    private $propertyAccessor;
    /** @var array */
    private $fields;
    /** @var MapperInterface */
    private $mapper;
    /** @var SerializerInterface */
    private $serializer;
    /** @var array */
    private $headers = [];

    /**
     * CSVGenerator constructor.
     *
     * @param PropertyAccessor $propertyAccessor
     * @param MapperInterface  $mapper
     */
    public function __construct(PropertyAccessor $propertyAccessor, MapperInterface $mapper)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->mapper = $mapper;
        $this->serializer = $this->getSerializer();
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(iterable $rows, array $headers = [], array $fields = []): string
    {
        $csvSource = [];

        $this->fields = $fields;
        $this->headers = $headers;
        empty($rows) and $rows[] = $headers;

        foreach ($rows as $row) {
            if (empty($row)) {
                continue;
            }
            $csvSource[] = $this->serializeRow($row);
        }

        return $this->serializer->serialize($csvSource, 'csv');
    }

    /**
     * @return Serializer|SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        if (null === $this->serializer) {
            $this->serializer = new Serializer([], [new CsvEncoder()]);
        }

        return $this->serializer;
    }

    /**
     * @param object $object
     *
     * @return array
     */
    private function serializeRow($object): array
    {
        return array_map(function (&$field) use ($object) {
            $headerPosition = array_search($field, $this->fields);
            $headerKey = $this->headers[$headerPosition] ?? null;

            if ($object instanceof SerializableReadModel) {
                $serialized = $object->serialize();

                return [$headerKey => isset($serialized[$field]) ? $this->mapper->map($field, $serialized[$field]) : ''];
            } else {
                return [$headerKey => $this->getRowValue($object, $field)];
            }
        }, $this->fields);
    }

    /**
     * @param object $object
     * @param string $field
     *
     * @return mixed
     */
    private function getRowValue($object, string $field)
    {
        if (is_array($object)) {
            return $this->mapper->map($field, $object[$field] ?? null);
        }

        return $this->mapper->map($field, $this->propertyAccessor->getValue($object, $field));
    }
}
