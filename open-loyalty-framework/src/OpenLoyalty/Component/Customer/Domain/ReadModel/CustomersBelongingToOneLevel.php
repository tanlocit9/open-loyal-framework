<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\LevelId;

/**
 * Class CustomersBelongingToOneLevel.
 */
class CustomersBelongingToOneLevel implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var LevelId
     */
    protected $levelId;

    /**
     * @var array
     */
    protected $customers = [];

    /**
     * CustomersBelongingToOneLevel constructor.
     *
     * @param LevelId $levelId
     */
    public function __construct(LevelId $levelId)
    {
        $this->levelId = $levelId;
    }

    /**
     * @param CustomerDetails $customer
     */
    public function addCustomer(CustomerDetails $customer): void
    {
        $this->customers[] = [
            'customerId' => (string) $customer->getCustomerId(),
            'firstName' => $customer->getFirstName(),
            'lastName' => $customer->getLastName(),
            'email' => $customer->getEmail(),
        ];
    }

    /**
     * @param CustomerId $customerId
     */
    public function removeCustomer(CustomerId $customerId): void
    {
        foreach ($this->customers as $key => $cust) {
            if ($cust['customerId'] == (string) $customerId) {
                unset($this->customers[$key]);
                break;
            }
        }
    }

    /**
     * @return array
     */
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * @return null|LevelId
     */
    public function getLevelId(): ?LevelId
    {
        return $this->levelId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->levelId;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $obj = new self(new LevelId($data['levelId']));
        $obj->customers = $data['customers'];

        return $obj;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        $customers = array_values($this->customers);

        return [
            'levelId' => $this->getId(),
            'customers' => $customers,
        ];
    }
}
