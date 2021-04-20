<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\Service;

use MockObject;
use OpenLoyalty\Bundle\SettingsBundle\Entity\SettingsEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManager;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use PHPUnit\Framework\TestCase;

/**
 * Class GeneralSettingsManagerTest.
 */
final class GeneralSettingsManagerTest extends TestCase
{
    /**
     * @test
     * @dataProvider getPointsActivationModes
     *
     * @param string   $pointsDaysExpiryMode
     * @param int|null $expected
     */
    public function it_returns_points_activation_days(string $pointsDaysExpiryMode, ?int $expected): void
    {
        /** @var GeneralSettingsManager|MockObject $generalSettingsManager */
        $generalSettingsManager = $this->getMockBuilder(GeneralSettingsManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getPointsDaysActive'])
            ->setMethods(['getDateTime', 'getSettingByKey'])
            ->getMock();

        $pointsDaysExpiryAfter = $this->getMockBuilder(SettingsEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pointsDaysExpiryAfter->method('getValue')->willReturn($pointsDaysExpiryMode);

        $pointsDaysActiveCount = $this->getMockBuilder(SettingsEntry::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $pointsDaysActiveCount->method('getValue')->willReturn(10);

        $generalSettingsManager->method('getSettingByKey')->will(
            $this->returnValueMap(
                [
                    ['pointsDaysExpiryAfter', $pointsDaysExpiryAfter],
                    ['pointsDaysActiveCount', $pointsDaysActiveCount],
                ]
            )
        );

        $generalSettingsManager->method('getDateTime')->will(
            $this->returnValueMap([
                [null, new \DateTime('2018-10-25')],
                ['last day of this month', new \DateTime('2018-10-31')],
                ['last day of december this year', new \DateTime('2018-12-31')],
            ])
        );

        $this->assertEquals($expected, $generalSettingsManager->getPointsDaysActive());
    }

    /**
     * @return array
     */
    public function getPointsActivationModes(): array
    {
        return [
            [AddPointsTransfer::TYPE_ALL_TIME_ACTIVE, null],
            [AddPointsTransfer::TYPE_AT_MONTH_END, 6], // calculate last day of the month
            [AddPointsTransfer::TYPE_AT_YEAR_END, 67], // calculate last day of the year
            [AddPointsTransfer::TYPE_AFTER_X_DAYS, 10],
            ['useDefaultValue', 90],
        ];
    }
}
