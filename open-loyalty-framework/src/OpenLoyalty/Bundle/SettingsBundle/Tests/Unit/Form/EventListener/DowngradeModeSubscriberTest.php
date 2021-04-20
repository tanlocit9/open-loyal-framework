<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\Form\EventListener;

use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\DowngradeModeSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Class DowngradeModeSubscriberTest.
 */
final class DowngradeModeSubscriberTest extends TestCase
{
    /**
     * @var FormInterface|MockObject
     */
    private $form;

    /**
     * @var DowngradeModeSubscriber
     */
    private $listener;

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

        $this->listener = new DowngradeModeSubscriber();

        $this->settings = new Settings();
    }

    /**
     * @test
     */
    public function it_does_not_validate_when_tier_assign_type_is_transaction(): void
    {
        $this->settings->addEntry(new StringSettingEntry('tierAssignType', 'transactions'));

        $this->form->expects($this->never())->method('get');

        $event = new FormEvent($this->form, $this->settings);

        $this->listener->__invoke($event);
    }

    /**
     * @test
     */
    public function it_does_not_validate_when_level_downgrade_mode_is_auto(): void
    {
        $this->settings->addEntry(new StringSettingEntry('tierAssignType', 'points'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeMode', 'automatic'));

        $this->form->expects($this->never())->method('get');

        $event = new FormEvent($this->form, $this->settings);

        $this->listener->__invoke($event);
    }

    /**
     * @test
     */
    public function it_does_not_validate_when_level_downgrade_mode_is_none(): void
    {
        $this->settings->addEntry(new StringSettingEntry('tierAssignType', 'points'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeMode', 'none'));

        $this->form->expects($this->never())->method('get');

        $event = new FormEvent($this->form, $this->settings);

        $this->listener->__invoke($event);
    }

    /**
     * @test
     */
    public function it_add_error_when_days_not_provided(): void
    {
        $this->settings = new Settings();
        $this->settings->addEntry(new StringSettingEntry('tierAssignType', 'points'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeMode', 'after_x_days'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeBase', 'active_points'));

        $levelDowngradeDaysForm = $this->createMock(FormInterface::class);
        $levelDowngradeDaysForm->expects($this->once())->method('addError');

        $this
            ->form
            ->expects($this->atLeast(1))
            ->method('get')
            ->with($this->equalTo('levelDowngradeDays'))
            ->willReturn(
                $levelDowngradeDaysForm
            )
        ;

        $event = new FormEvent($this->form, $this->settings);

        $this->listener->__invoke($event);
    }

    /**
     * @test
     */
    public function it_adds_error_when_days_less_than_one(): void
    {
        $this->settings->addEntry(new StringSettingEntry('tierAssignType', 'points'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeMode', 'after_x_days'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeBase', 'active_points'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeDays', 0));

        $levelDowngradeDaysForm = $this->createMock(FormInterface::class);
        $levelDowngradeDaysForm->expects($this->once())->method('addError');

        $this
            ->form
            ->expects($this->atLeast(1))
            ->method('get')
            ->with($this->equalTo('levelDowngradeDays'))
            ->willReturn(
                $levelDowngradeDaysForm
            )
        ;

        $event = new FormEvent($this->form, $this->settings);

        $this->listener->__invoke($event);
    }

    /**
     * @test
     */
    public function it_add_error_when_base_not_provided(): void
    {
        $this->settings->addEntry(new StringSettingEntry('tierAssignType', 'points'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeMode', 'after_x_days'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeDays', 1));

        $levelBaseForm = $this->createMock(FormInterface::class);
        $levelBaseForm->expects($this->once())->method('addError');

        $this
            ->form
            ->expects($this->atLeast(1))
            ->method('get')
            ->with($this->equalTo('levelDowngradeBase'))
            ->willReturn(
                $levelBaseForm
            )
        ;

        $event = new FormEvent($this->form, $this->settings);

        $this->listener->__invoke($event);
    }

    /**
     * @test
     */
    public function it_not_add_errors_when_valid(): void
    {
        $this->settings->addEntry(new StringSettingEntry('tierAssignType', 'points'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeMode', 'after_x_days'));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeDays', 1));
        $this->settings->addEntry(new StringSettingEntry('levelDowngradeBase', 'active_points'));

        $this->form->expects($this->never())->method('get');

        $event = new FormEvent($this->form, $this->settings);

        $this->listener->__invoke($event);
    }
}
