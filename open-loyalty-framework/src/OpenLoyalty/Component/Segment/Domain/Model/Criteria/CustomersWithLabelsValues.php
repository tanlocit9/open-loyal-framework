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
 * Class CustomersWithLabelsValues.
 */
class CustomersWithLabelsValues extends Criterion
{
    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param array $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    public static function fromArray(array $data)
    {
        $criterion = new self(new CriterionId($data['criterionId']));
        $criterion->setLabels($data['labels']);

        return $criterion;
    }

    public static function validate(array $data)
    {
        parent::validate($data);
        Assert::keyIsset($data, 'labels');
        Assert::notBlank($data, 'labels');
        Assert::isArray($data['labels']);
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return [
            'labels' => $this->getLabels(),
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Criterion::TYPE_CUSTOMER_WITH_LABELS_VALUES;
    }
}
