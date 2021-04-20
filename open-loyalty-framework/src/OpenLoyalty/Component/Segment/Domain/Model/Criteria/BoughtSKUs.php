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
 * Class BoughtSKUs.
 */
class BoughtSKUs extends Criterion
{
    /**
     * @var array
     */
    protected $skuIds = [];

    /**
     * @return array
     */
    public function getSkuIds()
    {
        return $this->skuIds;
    }

    /**
     * @param array $skuIds
     */
    public function setSkuIds($skuIds)
    {
        $this->skuIds = $skuIds;
    }

    public static function fromArray(array $data)
    {
        $criterion = new self(new CriterionId($data['criterionId']));
        $criterion->setSkuIds($data['skuIds']);

        return $criterion;
    }

    public static function validate(array $data)
    {
        parent::validate($data);
        Assert::keyIsset($data, 'skuIds');
        Assert::notBlank($data, 'skuIds');
        Assert::isArray($data['skuIds']);
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return [
            'skuIds' => $this->getSkuIds(),
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Criterion::TYPE_BOUGHT_SKUS;
    }
}
