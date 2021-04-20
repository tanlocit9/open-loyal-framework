<?php

namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Invitation;
use OpenLoyalty\Component\Customer\Domain\InvitationId;

/**
 * Class InvitationDetails.
 */
class InvitationDetails implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var InvitationId
     */
    private $invitationId;

    /**
     * @var CustomerId
     */
    private $referrerId;

    /**
     * @var string
     */
    private $referrerEmail;

    /**
     * @var string
     */
    private $referrerName;

    /**
     * @var string
     */
    private $recipientEmail;

    /**
     * @var CustomerId
     */
    private $recipientId;

    /**
     * @var string|null
     */
    private $recipientName;

    /**
     * @var string|null
     */
    private $recipientPhone;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $token;

    /**
     * /**
     * InvitationDetails constructor.
     *
     * @param InvitationId $invitationId
     * @param CustomerId   $referrerId
     * @param string       $referrerEmail
     * @param string       $referrerName
     * @param null|string  $recipientEmail
     * @param null|string  $recipientPhone
     * @param string       $token
     */
    public function __construct(
        InvitationId $invitationId,
        CustomerId $referrerId,
        string $referrerEmail,
        string $referrerName,
        ?string $recipientEmail,
        ?string $recipientPhone,
        string $token
    ) {
        $this->invitationId = $invitationId;
        $this->referrerId = $referrerId;
        $this->referrerEmail = $referrerEmail;
        $this->referrerName = $referrerName;
        $this->recipientEmail = $recipientEmail;
        $this->recipientPhone = $recipientPhone;
        $this->status = Invitation::STATUS_INVITED;
        $this->token = $token;
    }

    public function updateRecipientData(CustomerId $recipientId = null, $recipientName = null)
    {
        if ($recipientId) {
            if ($this->recipientId) {
                throw new \InvalidArgumentException('Already assigned to user');
            }
            $this->status = Invitation::STATUS_REGISTERED;
            $this->recipientId = $recipientId;
        }
        if ($recipientName) {
            $this->recipientName = $recipientName;
        }
    }

    /**
     * @return InvitationId
     */
    public function getInvitationId(): InvitationId
    {
        return $this->invitationId;
    }

    /**
     * @return CustomerId
     */
    public function getReferrerId(): CustomerId
    {
        return $this->referrerId;
    }

    /**
     * @return string
     */
    public function getReferrerEmail(): ?string
    {
        return $this->referrerEmail;
    }

    /**
     * @return string
     */
    public function getReferrerName(): string
    {
        return $this->referrerName;
    }

    /**
     * @return string|null
     */
    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    /**
     * @return CustomerId|null
     */
    public function getRecipientId(): ?CustomerId
    {
        return $this->recipientId;
    }

    /**
     * @return string
     */
    public function getRecipientName(): string
    {
        return $this->recipientName;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->invitationId;
    }

    /**
     * @return null|string
     */
    public function getRecipientPhone(): ?string
    {
        return $this->recipientPhone;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $invitation = new self(
            new InvitationId($data['invitationId']),
            new CustomerId($data['referrerId']),
            $data['referrerEmail'],
            $data['referrerName'],
            $data['recipientEmail'] ?? null,
           $data['recipientPhone'] ?? null,
            $data['token']
        );
        $invitation->updateRecipientData(
            $data['recipientId'] ? new CustomerId($data['recipientId']) : null,
            $data['recipientName']
        );

        $invitation->status = $data['status'];

        return $invitation;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'invitationId' => $this->invitationId->__toString(),
            'referrerId' => $this->referrerId->__toString(),
            'referrerEmail' => $this->referrerEmail,
            'referrerName' => $this->referrerName,
            'recipientId' => $this->recipientId ? $this->recipientId->__toString() : null,
            'recipientEmail' => $this->recipientEmail,
            'recipientPhone' => $this->recipientPhone,
            'recipientName' => $this->recipientName,
            'status' => $this->status,
            'token' => $this->token,
        ];
    }

    /**
     * @return string
     */
    public function referrerIdAsString(): string
    {
        return (string) $this->referrerId;
    }

    /**
     * @return string
     */
    public function recipientIdAsString(): string
    {
        return (string) $this->recipientId;
    }

    /**
     * Made purchase.
     */
    public function madePurchase(): void
    {
        $this->status = Invitation::STATUS_MADE_PURCHASE;
    }
}
