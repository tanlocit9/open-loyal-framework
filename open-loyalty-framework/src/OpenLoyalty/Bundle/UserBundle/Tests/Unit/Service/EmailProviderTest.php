<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Service;

use OpenLoyalty\Bundle\EmailBundle\Model\MessageInterface;
use OpenLoyalty\Bundle\EmailBundle\Service\MessageFactoryInterface;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\Seller;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\EmailBundle\Mailer\OloyMailer;
use OpenLoyalty\Bundle\UserBundle\Service\EmailProvider;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\Model\Reward;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmailProviderTest extends TestCase
{
    /**
     * @var OloyMailer|MockObject
     */
    private $mailer;

    /**
     * @var MessageFactoryInterface|MockObject
     */
    private $messageFactory;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var MessageInterface|MockObject
     */
    private $message;

    /**
     * @var GeneralSettingsManagerInterface|MockObject
     */
    private $generalSettingsManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->generalSettingsManager = $this->getMockBuilder(GeneralSettingsManagerInterface::class)
            ->getMock();
        $this->generalSettingsManager->method('getProgramName')->willReturn('Test program');
        $this->mailer = $this->getMockBuilder(OloyMailer::class)->disableOriginalConstructor()->getMock();
        $this->message = $this->getMockBuilder(MessageInterface::class)->disableOriginalConstructor()->getMock();
        $this->messageFactory = $this->getMockBuilder(MessageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageFactory->method('create')->willReturn($this->message);

        $this->parameters = [
            'from_address' => 'from@mail.com',
            'from_name' => 'from name',
            'reset_password_url' => 'reset_password_url',
            'admin_url' => 'admin_url',
            'customer_url' => 'customer_url',
            'merchant_url' => 'merchant_url',
            'loyalty_program_name' => 'Test program',
            'ecommerce_address' => 'http://ecommerce.test',
            'customer_panel_url' => 'http://customer.panel',
        ];
    }

    /**
     * @test
     * @dataProvider emailMessageProvider
     *
     * @param string $subject
     * @param string $email
     * @param string $template
     * @param array  $params
     */
    public function it_sends_message(string $subject, string $email, string $template = null, array $params = [])
    {
        $this->messageFactory->expects($this->once())->method('create')->willReturn($this->message);

        $this->message->expects($this->once())->method('setSubject')->with($subject);
        $this->message->expects($this->once())->method('setRecipientEmail')->with($email);
        $this->message->expects($this->once())->method('setRecipientName')->with($email);
        $this->message->expects($this->once())->method('setSenderEmail')->with($this->parameters['from_address']);
        $this->message->expects($this->once())->method('setSenderName')->with($this->parameters['from_name']);
        $this->message->expects($this->once())->method('setTemplate')->with($template);
        $this->message->expects($this->once())->method('setParams')->with($params);

        $this->getEmailProviderMock(null, ['sendMessage'])
             ->sendMessage($subject, $email, $template, $params);
    }

    /**
     * @return array
     */
    public function emailMessageProvider()
    {
        return [
            ['subject', 'example@example.com', 'template', ['params']],
            ['subject', 'example@example.com'],
        ];
    }

    /**
     * @test
     */
    public function it_sends_registration_with_temporary_password_mail()
    {
        $user = $this->getCustomerDetailsMock();
        $user->expects($this->atLeast(2))->method('getEmail')->willReturn('example@example.com');
        $user->expects($this->atLeastOnce())->method('getPhone')->willReturn('123455668990');
        $user->expects($this->atLeastOnce())->method('getLoyaltyCardNumber')->willReturn('aaabbbccc');

        $emailProvider = $this->getEmailProviderMock(['sendMessage']);
        $emailProvider->expects($this->once())->method('sendMessage');

        $emailProvider->registrationWithTemporaryPassword($user, 'testpass');
    }

    /**
     * @test
     */
    public function it_sends_registration_mail()
    {
        $user = $this->getUserMock();
        $user->expects($this->atLeast(2))->method('getEmail')->willReturn('user@example.com');

        $emailProvider = $this->getEmailProviderMock(['sendMessage']);
        $emailProvider->method('sendMessage')->willReturn(true);
        $emailProvider->expects($this->once())->method('sendMessage');

        $emailProvider->registration($user, 'http://url.test');
    }

    /**
     * @test
     * @dataProvider getResetPasswordDataProvider
     *
     * @param string $userClass
     * @param string $resetUrl
     */
    public function it_sends_password_reset_email(string $userClass, string $resetUrl): void
    {
        /** @var MockObject|User $user */
        $user = $this->getMockBuilder($userClass)->disableOriginalConstructor()->getMock();
        $user->expects($this->atLeastOnce())->method('getEmail')->willReturn('user@example.com');
        $user->expects($this->atLeastOnce())->method('getConfirmationToken')->willReturn('1234');

        $emailProvider = $this->getEmailProviderMock(['sendMessage']);
        $emailProvider->expects($this->once())->method('sendMessage')->with(
            'Password reset requested',
            'user@example.com',
            'OpenLoyaltyUserBundle:email:password_reset.html.twig',
            [
                'program_name' => 'Test program',
                'url_reset_password' => $resetUrl.'/1234',
            ]
        );

        $emailProvider->resettingPasswordMessage($user);
    }

    public function getResetPasswordDataProvider()
    {
        return [
            [Admin::class, 'admin_urlreset_password_url'],
            [Customer::class, 'customer_urlreset_password_url'],
            [Seller::class, 'merchant_urlreset_password_url'],
            [User::class, 'admin_urlreset_password_url'],
        ];
    }

    /**
     * @test
     */
    public function it_sends_email_after_campaign_purchase()
    {
        $customerDetails = $this->getCustomerDetailsMock();
        $customerDetails->expects($this->atLeastOnce())->method('getEmail')->willReturn('user@example.com');

        $campaign = $this->getCampaignMock();
        $campaign->expects($this->once())->method('getName')->willReturn('Test reward');
        $campaign->expects($this->once())->method('getUsageInstruction')->willReturn('Instruction');

        $coupon = $this->getCouponMock();
        $coupon->expects($this->once())->method('getCode')->willReturn('1234');

        $emailProvider = $this->getEmailProviderMock(['sendMessage']);
        $emailProvider->expects($this->once())->method('sendMessage');

        $emailProvider->customerBoughtCampaign($customerDetails, $campaign, $coupon);
    }

    /**
     * @test
     */
    public function it_sends_add_points_to_customer_email()
    {
        $customerDetails = $this->getCustomerDetailsMock();
        $customerDetails->expects($this->atLeastOnce())->method('getEmail')->willReturn('user@example.com');

        $emailProvider = $this->getEmailProviderMock(['sendMessage']);
        $emailProvider->expects($this->once())->method('sendMessage');

        $emailProvider->addPointsToCustomer($customerDetails, 112, 12);
    }

    /**
     * @test
     */
    public function it_sends_move_to_level_email()
    {
        $customerDetails = $this->getCustomerDetailsMock();
        $customerDetails->expects($this->atLeastOnce())->method('getEmail')->willReturn('user@example.com');

        $reward = $this->getRewardMock();
        $reward->expects($this->once())->method('getValue')->willReturn(0.3);

        $level = $this->getLevelMock();
        $level->expects($this->once())->method('getName')->willReturn('New level');
        $level->expects($this->once())->method('getReward')->willReturn($reward);

        $emailProvider = $this->getEmailProviderMock(['sendMessage']);
        $emailProvider->expects($this->once())->method('sendMessage');

        $emailProvider->moveToLevel($customerDetails, $level);
    }

    /**
     * @param array|null $methods
     * @param array|null $methodsExcept
     *
     * @return MockObject|EmailProvider
     */
    public function getEmailProviderMock(array $methods = null, array $methodsExcept = null)
    {
        /** @var MockBuilder $emailProvider */
        $emailProvider = $this->getMockBuilder(EmailProvider::class)->setConstructorArgs(
            [
                $this->generalSettingsManager,
                $this->messageFactory,
                $this->mailer,
                $this->parameters,
            ]
        );

        if (!empty($methods)) {
            $emailProvider->setMethods($methods);
        }
        if (!empty($methodsExcept)) {
            $emailProvider->setMethodsExcept($methodsExcept);
        }

        return $emailProvider->getMock();
    }

    /**
     * @return MockObject|CustomerDetails
     */
    public function getCustomerDetailsMock()
    {
        return $this->getMockBuilder(CustomerDetails::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @return MockObject|User
     */
    public function getUserMock()
    {
        return $this->getMockBuilder(User::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @return MockObject|Campaign
     */
    public function getCampaignMock()
    {
        return $this->getMockBuilder(Campaign::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @return MockObject|Coupon
     */
    public function getCouponMock()
    {
        return $this->getMockBuilder(Coupon::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @return MockObject|Level
     */
    public function getLevelMock()
    {
        return $this->getMockBuilder(Level::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @return MockObject|Reward
     */
    public function getRewardMock()
    {
        return $this->getMockBuilder(Reward::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
