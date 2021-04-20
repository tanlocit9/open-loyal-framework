<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Model\Response;

/**
 * Class Criterion.
 */
class Criterion
{
    /**
     * @var string
     */
    protected $criterionId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    private $data;

    /**
     * Criterion constructor.
     *
     * @param string $criterionId
     * @param string $type
     * @param array  $data
     */
    public function __construct(string $criterionId, string $type, array $data)
    {
        $this->criterionId = $criterionId;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getCriterionId(): string
    {
        return $this->criterionId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
