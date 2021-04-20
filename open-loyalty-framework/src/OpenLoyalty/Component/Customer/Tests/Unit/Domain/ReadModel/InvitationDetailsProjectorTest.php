<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\ReadModel;

use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Projector;
use Broadway\ReadModel\Testing\ProjectorScenarioTestCase;
use Broadway\Repository\Repository;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasAttachedToInvitation;
use OpenLoyalty\Component\Customer\Domain\Event\InvitationWasCreated;
use OpenLoyalty\Component\Customer\Domain\Event\PurchaseWasMadeForThisInvitation;
use OpenLoyalty\Component\Customer\Domain\Invitation;
use OpenLoyalty\Component\Customer\Domain\InvitationId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetailsProjector;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class InvitationDetailsProjectorTest.
 */
final class InvitationDetailsProjectorTest extends ProjectorScenarioTestCase
{
    /**
     * @var InvitationId
     */
    protected $invitationId;

    /**
     * @var CustomerId
     */
    protected $referrerId;

    /**
     * @var string|null
     */
    protected $recipientEmail;

    /**
     * @var string|null
     */
    protected $recipientPhone;

    /**
     * @var string
     */
    protected $token;

    /**
     * {@inheritdoc}
     */
    protected function createProjector(InMemoryRepository $repository): Projector
    {
        $this->invitationId = new InvitationId('00000000-0000-0000-0000-000000000000');
        $this->referrerId = new CustomerId('00000000-0000-0000-0000-000000000001');
        $this->recipientEmail = 'andrew.doe@example.com';
        $this->recipientPhone = '123123777';
        $this->token = 'token';

        /** @var Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)->getMock();
        $customer->method('getEmail')->willReturn('john.doe@example.com');
        $customer->method('getFirstName')->willReturn('John');
        $customer->method('getLastName')->willReturn('Doe');

        /** @var Repository|MockObject $customerRepository */
        $customerRepository = $this->getMockBuilder(CustomerRepository::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $customerRepository->method('load')->willReturn($customer);

        return new InvitationDetailsProjector(
            $customerRepository,
            $repository
        );
    }

    /**
     * @test
     */
    public function it_creates_an_email_invitation(): void
    {
        $this->scenario->given([
        ])->when(new InvitationWasCreated(
                $this->invitationId,
                $this->referrerId,
                $this->recipientEmail,
                null,
                $this->token
            )
        )->then([
            InvitationDetails::deserialize(
                $this->getInvitationDetailsData(
                    'john.doe@example.com',
                    'John Doe',
                    Invitation::STATUS_INVITED
                )
            ),
        ]);
    }

    /**
     * @test
     */
    public function it_creates_an_mobile_invitation(): void
    {
        $this->scenario->given([
        ])->when(new InvitationWasCreated(
                $this->invitationId,
                $this->referrerId,
                null,
                $this->recipientPhone,
                $this->token
            )
        )->then([
            InvitationDetails::deserialize(
                $this->getInvitationDetailsData(
                    'john.doe@example.com',
                    'John Doe',
                    Invitation::STATUS_INVITED,
                    null,
                    null,
                    Invitation::MOBILE_TYPE
                )
            ),
        ]);
    }

    /**
     * @test
     */
    public function it_attaches_an_invitation_to_the_customer(): void
    {
        $this->scenario->given([
            new InvitationWasCreated(
                $this->invitationId,
                $this->referrerId,
                $this->recipientEmail,
                null,
                $this->token
            ),
        ])->when(new CustomerWasAttachedToInvitation(
                $this->invitationId,
                $this->referrerId
            )
        )->then([
            InvitationDetails::deserialize(
                $this->getInvitationDetailsData(
                    'john.doe@example.com',
                    'John Doe',
                    Invitation::STATUS_REGISTERED,
                    $this->referrerId,
                    'John Doe'
                )
            ),
        ]);
    }

    /**
     * @test
     */
    public function it_updates_invitation_when_purchases_was_made_for_this_invitation(): void
    {
        $this->scenario->given([
            new InvitationWasCreated(
                $this->invitationId,
                $this->referrerId,
                $this->recipientEmail,
                null,
                $this->token
            ),
        ])->when(new PurchaseWasMadeForThisInvitation(
                $this->invitationId
            )
        )->then([
            InvitationDetails::deserialize(
                $this->getInvitationDetailsData(
                    'john.doe@example.com',
                    'John Doe',
                    Invitation::STATUS_MADE_PURCHASE
                )
            ),
        ]);
    }

    /**
     * @param string          $referrerEmail
     * @param string          $referrerName
     * @param string          $status
     * @param CustomerId|null $recipientId
     * @param string|null     $recipientName
     * @param string          $type
     *
     * @return array
     */
    protected function getInvitationDetailsData(
        string $referrerEmail,
        string $referrerName,
        string $status,
        CustomerId $recipientId = null,
        string $recipientName = null,
        string $type = Invitation::EMAIL_TYPE
    ): array {
        return [
            'invitationId' => (string) $this->invitationId,
            'referrerId' => (string) $this->referrerId,
            'recipientEmail' => $type === Invitation::EMAIL_TYPE ? $this->recipientEmail : null,
            'recipientPhone' => $type === Invitation::MOBILE_TYPE ? $this->recipientPhone : null,
            'token' => $this->token,
            'recipientId' => (string) $recipientId,
            'recipientName' => $recipientName,
            'referrerEmail' => $referrerEmail,
            'referrerName' => $referrerName,
            'status' => $status,
        ];
    }
}
