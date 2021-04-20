<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Model\Criteria;

use OpenLoyalty\Component\Segment\Domain\CriterionId;
use Assert\Assertion as Assert;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;

/**
 * Class BoughtMakers.
 */
class BoughtMakers extends Criterion
{
    /**
     * @var array
     */
    protected $makers = [];

    /**
     * @return array
     */
    public function getMakers()
    {
        return $this->makers;
    }

    /**
     * @param array $makers
     */
    public function setMakers($makers)
    {
        $this->makers = $makers;
    }

    public static function fromArray(array $data)
    {
        $criterion = new self(new CriterionId($data['criterionId']));
        $criterion->setMakers($data['makers']);

        return $criterion;
    }

    public static function validate(array $data)
    {
        parent::validate($data);
        Assert::keyIsset($data, 'makers');
        Assert::notBlank($data, 'makers');
        Assert::isArray($data['makers']);
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return [
            'makers' => $this->getMakers(),
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Criterion::TYPE_BOUGHT_MAKERS;
    }
}
