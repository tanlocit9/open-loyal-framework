<?php

namespace OpenLoyalty\Bundle\PointsBundle\Tests\Integration\Service;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignValidator;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsageRepository;
use OpenLoyalty\Component\Customer\Domain\Model\Status;
use PHPUnit\Framework\TestCase;

/**
 * Class CampaignValidatorTest.
 */
class CampaignValidatorTest extends TestCase
{
    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\CampaignBundle\Exception\NotEnoughPointsException
     */
    public function it_throws_exception_when_there_is_not_enough_points(): void
    {
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(10), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 20]);
        $validator->checkIfCustomerHasEnoughPoints($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_there_is_enough_points(): void
    {
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(10), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $result = $validator->checkIfCustomerHasEnoughPoints($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'));
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\CampaignBundle\Exception\NotEnoughPointsException
     */
    public function it_throws_exception_when_there_is_not_enough_points_for_few_coupons(): void
    {
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 20]);
        $validator->checkIfCustomerHasEnoughPoints($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 6);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_ignores_quantity_for_cashback_points(): void
    {
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 20]);
        $campaign->setReward(Campaign::REWARD_TYPE_CASHBACK);
        $validator->checkIfCustomerHasEnoughPoints($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 6);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_ignores_quantity_for_percentage_discount_points(): void
    {
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 20]);
        $campaign->setReward(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);
        $validator->checkIfCustomerHasEnoughPoints($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 6);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_there_is_enough_points_for_few_coupons(): void
    {
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 20]);
        $validator->checkIfCustomerHasEnoughPoints($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 5);
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\CampaignBundle\Exception\NoCouponsLeftException
     */
    public function it_throws_exception_when_campaign_is_unlimited_and_there_is_no_coupons_left(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(true);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(2, 0), $this->getAccountDetailsRepository(10), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_campaign_is_unlimited_and_there_are_coupons_left(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(true);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(1, 0), $this->getAccountDetailsRepository(10), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_ignores_quantity_for_cashback_limits(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setReward(Campaign::REWARD_TYPE_CASHBACK);
        $campaign->setUnlimited(false);
        $campaign->setLimit(1);
        $campaign->setLimitPerUser(10);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(1000), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 2);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_ignores_quantity_for_percentage_discount_limits(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setReward(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);
        $campaign->setUnlimited(false);
        $campaign->setLimit(1);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(1000), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 2);
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\CampaignBundle\Exception\NoCouponsLeftException
     */
    public function it_throws_exception_when_campaign_is_unlimited_and_there_is_no_coupons_left_for_few_coupons(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(true);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(1, 0), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 2);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_campaign_is_unlimited_and_there_are_coupons_left_for_few_coupons(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(true);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 2);
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignLimitExceededException
     */
    public function it_throws_exception_when_campaign_is_limited_and_limit_is_exceeded(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(false);
        $campaign->setLimit(1);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(1, 0), $this->getAccountDetailsRepository(10), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_campaign_is_limited_and_limit_is_not_exceeded(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(false);
        $campaign->setLimit(1);
        $campaign->setLimitPerUser(10);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 0), $this->getAccountDetailsRepository(10), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'));
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignLimitExceededException
     */
    public function it_throws_exception_when_campaign_is_limited_and_limit_is_exceeded_for_few_coupons(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(false);
        $campaign->setLimit(2);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234'), new Coupon('12342')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(1, 0), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 2);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_campaign_is_limited_and_limit_is_not_exceeded_for_few_coupons(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(false);
        $campaign->setLimit(3);
        $campaign->setLimitPerUser(10);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234'), new Coupon('12343')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(1, 0), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 2);
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignLimitPerCustomerExceededException
     */
    public function it_throws_exception_when_campaign_is_limited_and_limit_for_user_is_exceeded(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(false);
        $campaign->setLimit(1);
        $campaign->setLimitPerUser(10);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 10), $this->getAccountDetailsRepository(10), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'));
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignLimitPerCustomerExceededException
     */
    public function it_throws_exception_when_campaign_is_limited_and_limit_for_user_is_exceeded_for_few_coupons(): void
    {
        $campaign = new Campaign(new CampaignId('00000000-0000-474c-b092-b0dd880c07e1'), ['costInPoints' => 10]);
        $campaign->setUnlimited(false);
        $campaign->setLimit(5);
        $campaign->setLimitPerUser(10);
        $campaign->setCoupons([new Coupon('123'), new Coupon('1234')]);
        $validator = new CampaignValidator($this->getCouponUsageRepository(0, 8), $this->getAccountDetailsRepository(100), $this->getSettingsManager([Status::TYPE_ACTIVE]));
        $validator->validateCampaignLimits($campaign, new CustomerId('00000000-0000-474c-b092-b0dd880c07e1'), 3);
    }

    protected function getCouponUsageRepository($usage, $customerUsage)
    {
        $repo = $this->getMockBuilder(CouponUsageRepository::class)->getMock();
        $repo->method('countUsageForCampaign')->with($this->isInstanceOf(CampaignId::class))
            ->willReturn($usage);
        $repo->method('countUsageForCampaignAndCustomer')->with(
            $this->isInstanceOf(CampaignId::class),
            $this->isInstanceOf(CustomerId::class)
        )->willReturn($customerUsage);

        return $repo;
    }

    protected function getAccountDetailsRepository($points)
    {
        $repo = $this->getMockBuilder(Repository::class)->getMock();
        $account = $this->getMockBuilder(AccountDetails::class)->disableOriginalConstructor()->getMock();
        $account->method('getAvailableAmount')->willReturn($points);
        $repo->method('findBy')->with($this->arrayHasKey('customerId'))
            ->willReturn([$account]);

        return $repo;
    }

    protected function getSettingsManager(array $statuses)
    {
        $settingsManager = $this->getMockBuilder(SettingsManager::class)->getMock();
        $settingsManager->method('getSettingByKey')->willReturn($statuses);

        return $settingsManager;
    }
}
