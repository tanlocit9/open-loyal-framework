<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;

/**
 * Class ChangeDeliveryStatusCommandHandler.
 */
class ChangeDeliveryStatusCommandHandler extends SimpleCommandHandler
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * ChangeDeliveryStatusCommandHandler constructor.
     *
     * @param CustomerRepository $repository
     */
    public function __construct(CustomerRepository $repository)
    {
        $this->customerRepository = $repository;
    }

    /**
     * @param ChangeDeliveryStatusCommand $command
     */
    public function handleChangeDeliveryStatusCommand(ChangeDeliveryStatusCommand $command): void
    {
        /** @var Customer $customer */
        $customer = $this->customerRepository->load($command->getCustomerId());
        $customer->changeCampaignBoughtDeliveryStatus($command->getCouponId(), $command->getStatus());

        $this->customerRepository->save($customer);
    }
}
