<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\Form\EventListener;

use OpenLoyalty\Bundle\SettingsBundle\Entity\BooleanSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Entity\IntegerSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\DowngradeModeNoneResetAfterDaysFieldSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Class DowngradeModeNoneResetAfterDaysFieldSubscriberTest.
 */
final class DowngradeModeNoneResetAfterDaysFieldSubscriberTest extends TestCase
{
    /**
     * @var DowngradeModeNoneResetAfterDaysFieldSubscriber
     */
    private $listener;

    /**
     * @var FormInterface|MockObject
     */
    private $form;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->form = $this->createMock(FormInterface::class);

        $this->listener = new DowngradeModeNoneResetAfterDaysFieldSubscriber();

        $this->settings = new Settings();
    }

    /**
     * @test
     */
    public function it_returns_void_when_form_data_is_not_instance_of_settings(): void
    {
        $data = new \DateTime();

        $this->form->setData($data);
        $this->form->expects($this->never())->method('get');

        $event = new FormEvent($this->form, $data);
        $this->listener->__invoke($event);
    }

    /**
     * @test
     */
    public function it_resets_downgrade_settings_when_level_will_be_calculated_with_transactions(): void
    {
        $this->setDefaultSettingsEntry('transactions', 'automatic', 'active_points');

        $event = new FormEvent($this->form, $this->settings);
        $this->listener->__invoke($event);

        /** @var Settings $data */
        $data = $event->getData();

        $this->assertSame('none', $data->getEntry('levelDowngradeBase')->getValue());
        $this->assertNull($data->getEntry('levelDowngradeDays')->getValue());
        $this->assertSame('none', $data->getEntry('levelDowngradeMode')->getValue());
        $this->assertNull($data->getEntry('levelResetPointsOnDowngrade')->getValue());
    }

    /**
     * @test
     */
    public function it_resets_value_of_downgrade_every_days_and_based_on_settings_when_mode_is_none(): void
    {
        $this->setDefaultSettingsEntry('points', 'none', 'active_points');

        $event = new FormEvent($this->form, $this->settings);
        $this->listener->__invoke($event);

        /** @var Settings $data */
        $data = $event->getData();

        $this->assertSame('none', $data->getEntry('levelDowngradeMode')->getValue());
        $this->assertSame('none', $data->getEntry('levelDowngradeBase')->getValue());
        $this->assertNull($data->getEntry('levelDowngradeDays')->getValue());
        $this->assertNull($data->getEntry('levelResetPointsOnDowngrade')->getValue());
    }

    /**
     * @test
     */
    public function it_resets_downgrade_settings_settings_when_mode_is_automatic(): void
    {
        $this->setDefaultSettingsEntry('points', 'automatic', 'active_points');

        $event = new FormEvent($this->form, $this->settings);
        $this->listener->__invoke($event);

        /** @var Settings $data */
        $data = $event->getData();

        $this->assertSame('automatic', $data->getEntry('levelDowngradeMode')->getValue());
        $this->assertSame('none', $data->getEntry('levelDowngradeBase')->getValue());
        $this->assertNull($data->getEntry('levelDowngradeDays')->getValue());
        $this->assertNull($data->getEntry('levelResetPointsOnDowngrade')->getValue());
    }

    /**
     * @test
     */
    public function it_does_not_reset_downgrade_settings_when_mode_is_every_x_days(): void
    {
        $this->setDefaultSettingsEntry('points', 'after_x_days', 'active_points');

        $event = new FormEvent($this->form, $this->settings);
        $this->listener->__invoke($event);

        /** @var Settings $data */
        $data = $event->getData();

        $this->assertSame('after_x_days', $data->getEntry('levelDowngradeMode')->getValue());
        $this->assertSame('active_points', $data->getEntry('levelDowngradeBase')->getValue());
        $this->assertSame(10, $data->getEntry('levelDowngradeDays')->getValue());
        $this->assertTrue($data->getEntry('levelResetPointsOnDowngrade')->getValue());
    }

    /**
     * @param string $assignType
     * @param string $downgradeMode
     * @param string $downgradeBase
     */
    private function setDefaultSettingsEntry(string $assignType, string $downgradeMode, string $downgradeBase): void
    {
        $this->settings->addEntry(new StringSettingEntry('tierAssignType', $assignType));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeMode', $downgradeMode));
        $this->settings->addEntry(new IntegerSettingEntry('levelDowngradeDays', 10));
        $this->settings->addEntry(new IntegerSettingEntry('levelDowngradeBase', $downgradeBase));
        $this->settings->addEntry(new BooleanSettingEntry('levelResetPointsOnDowngrade', true));
    }
}
