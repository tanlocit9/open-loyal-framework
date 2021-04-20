<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Component\Customer\Domain\Model\Status;

/**
 * Class AvailableCustomerStatusesChoices.
 */
class AvailableCustomerStatusesChoices implements ChoiceProvider
{
    /**
     * @var Status
     */
    protected $status;

    /**
     * AvailableCustomerStatusesChoices constructor.
     *
     * @param Status $status
     */
    public function __construct(Status $status)
    {
        $this->status = $status;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $availableCustomerStatusesList = $this->status->getAvailableStatuses();

        return ['choices' => $availableCustomerStatusesList];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'availableCustomerStatuses';
    }
}
