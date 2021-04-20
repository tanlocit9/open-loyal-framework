<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Customer.
 *
 * @ORM\Entity()
 */
class Customer extends User
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="temporary_password_set_at", nullable=true)
     */
    private $temporaryPasswordSetAt;

    /**
     * @ORM\Column(name="action_token", type="string", length = 20, nullable = true)
     */
    private $actionToken;

    /**
     * @ORM\Column(name="referral_customer_email", type="string", length = 128, nullable= true)
     */
    private $referralCustomerEmail;

    /**
     * @ORM\Column(name="newsletter_used_flag", type="boolean")
     */
    private $newsletterUsedFlag;

    /**
     * @var Status
     *
     * @ORM\Embedded(class = "Status", columnPrefix = "status_" )
     */
    private $status;

    /**
     * @var null|string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"registration"})
     * @JMS\Expose()
     */
    private $phone;

    /**
     * Customer constructor.
     *
     * @param CustomerId $id
     */
    public function __construct(CustomerId $id)
    {
        parent::__construct((string) $id);
    }

    /**
     * @return mixed
     */
    public function getActionToken()
    {
        return $this->actionToken;
    }

    /**
     * @param mixed $actionToken
     */
    public function setActionToken($actionToken)
    {
        $this->actionToken = $actionToken;
    }

    /**
     * @return null|\DateTime
     */
    public function getTemporaryPasswordSetAt(): ?\DateTime
    {
        return $this->temporaryPasswordSetAt;
    }

    /**
     * @param null|\DateTime $temporaryPasswordSetAt
     */
    public function setTemporaryPasswordSetAt(?\DateTime $temporaryPasswordSetAt): void
    {
        $this->temporaryPasswordSetAt = $temporaryPasswordSetAt;
    }

    /**
     * @return string
     */
    public function getReferralCustomerEmail(): ?string
    {
        return $this->referralCustomerEmail;
    }

    /**
     * @param null|string $referralCustomerEmail
     */
    public function setReferralCustomerEmail(?string $referralCustomerEmail): void
    {
        $this->referralCustomerEmail = $referralCustomerEmail;
    }

    /**
     * @return bool
     */
    public function getNewsletterUsedFlag(): bool
    {
        return boolval($this->newsletterUsedFlag);
    }

    /**
     * @param bool $newsletterUsedFlag
     */
    public function setNewsletterUsedFlag(bool $newsletterUsedFlag): void
    {
        $this->newsletterUsedFlag = $newsletterUsedFlag;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->getStatus() && $this->getStatus()->getType() === Status::TYPE_NEW;
    }

    /**
     * @param Status $status
     */
    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     */
    public function setPhone(?string $phone = null): void
    {
        $this->phone = $phone;
    }
}
