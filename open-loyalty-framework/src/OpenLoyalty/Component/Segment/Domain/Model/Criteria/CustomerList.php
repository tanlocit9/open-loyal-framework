<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Model\Criteria;

use OpenLoyalty\Component\Segment\Domain\CriterionId;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use Assert\Assertion as Assert;

/**
 * Class CustomerList.
 */
class CustomerList extends Criterion
{
    /**
     * @var array
     */
    private $customers;

    /**
     * @return array
     */
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * @param array $customers
     */
    public function setCustomers(array $customers): void
    {
        $this->customers = $customers;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data)
    {
        $criterion = new self(new CriterionId($data['criterionId']));
        $criterion->setCustomers($data['customers']);

        return $criterion;
    }

    /**
     * {@inheritdoc}
     */
    public static function validate(array $data)
    {
        parent::validate($data);
        Assert::keyIsset($data, 'customers');
        Assert::notBlank($data, 'customers');
        Assert::isArray($data['customers']);
        Assert::allString($data['customers']);
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return [
            'customers' => $this->getCustomers(),
        ];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Criterion::TYPE_CUSTOMER_LIST;
    }
}
