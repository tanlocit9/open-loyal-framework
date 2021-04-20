<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Form\DataTransformer;

use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CustomerDataTransformer.
 */
class CustomerDataTransformer implements DataTransformerInterface
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * TransferPointsFormType constructor.
     *
     * @param CustomerDetailsRepository $customerDetailsRepository
     */
    public function __construct(CustomerDetailsRepository $customerDetailsRepository)
    {
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($text)
    {
        if (!$text) {
            return null;
        }
        $criteria = [
            'id' => $text,
            'phone' => $text,
            'email' => $text,
        ];

        $customers = $this->customerDetailsRepository->findByAnyCriteria($criteria);
        $filtered = array_filter($customers, function (CustomerDetails $customerDetails) use ($text) {
            return $customerDetails->getId() === $text;
        });
        $customer = count($filtered) > 0 ? reset($filtered) : null;
        if ($customer) {
            return $customer;
        }
        $filtered = array_filter($customers, function (CustomerDetails $customerDetails) use ($text) {
            return $customerDetails->getEmail() === $text;
        });
        $customer = count($filtered) > 0 ? reset($filtered) : null;
        if ($customer) {
            return $customer;
        }
        $filtered = array_filter($customers, function (CustomerDetails $customerDetails) use ($text) {
            return $customerDetails->getPhone() === $text;
        });

        return count($filtered) > 0 ? reset($filtered) : null;
    }
}
