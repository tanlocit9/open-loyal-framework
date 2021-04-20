<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Webhook\Tests\Unit\Infrastructure\Client\Request\Header;

use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\DoctrineSettingsManager;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Webhook\Infrastructure\Client\Request\Header\DefaultRequestHeader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DefaultRequestHeaderTest.
 */
class DefaultRequestHeaderTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_default_headers(): void
    {
        /** @var DoctrineSettingsManager|MockObject $settingsManagerMock */
        $settingsManagerMock = $this
            ->getMockBuilder(SettingsManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var StringSettingEntry|MockObject $headerNameSettingEntryMock */
        $headerNameSettingEntryMock = $this
            ->getMockBuilder(StringSettingEntry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $headerNameSettingEntryMock->method('getValue')->willReturn('');

        /** @var StringSettingEntry|MockObject $headerValueSettingEntryMock */
        $headerValueSettingEntryMock = $this
            ->getMockBuilder(StringSettingEntry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $headerValueSettingEntryMock->method('getValue')->willReturn('');

        $settingsManagerMock
            ->method('getSettingByKey')
            ->willReturnCallback(
                function (string $param) use ($headerNameSettingEntryMock, $headerValueSettingEntryMock): StringSettingEntry {
                    switch ($param) {
                        case 'webhookHeaderName':
                            return $headerNameSettingEntryMock;
                        case 'webhookHeaderValue':
                            return $headerValueSettingEntryMock;
                    }

                    throw new \InvalidArgumentException();
                }
            )
        ;

        $defaultRequestHeader = new DefaultRequestHeader($settingsManagerMock);

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'OpenLoyalty',
        ];

        $this->assertSame($requestHeaders, $defaultRequestHeader->headers());
    }

    /**
     * @test
     */
    public function it_returns_default_headers_with_additional_header_from_settings(): void
    {
        /** @var DoctrineSettingsManager|MockObject $settingsManagerMock */
        $settingsManagerMock = $this
            ->getMockBuilder(SettingsManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var StringSettingEntry|MockObject $headerNameSettingEntryMock */
        $headerNameSettingEntryMock = $this
            ->getMockBuilder(StringSettingEntry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $headerNameSettingEntryMock->method('getValue')->willReturn('Content-Test');

        /** @var StringSettingEntry|MockObject $headerValueSettingEntryMock */
        $headerValueSettingEntryMock = $this
            ->getMockBuilder(StringSettingEntry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $headerValueSettingEntryMock->method('getValue')->willReturn('1234');

        $settingsManagerMock
            ->method('getSettingByKey')
            ->willReturnCallback(
                function (string $param) use ($headerNameSettingEntryMock, $headerValueSettingEntryMock): StringSettingEntry {
                    switch ($param) {
                        case 'webhookHeaderName':
                            return $headerNameSettingEntryMock;
                        case 'webhookHeaderValue':
                            return $headerValueSettingEntryMock;
                    }

                    throw new \InvalidArgumentException();
                }
            )
        ;

        $defaultRequestHeader = new DefaultRequestHeader($settingsManagerMock);

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'OpenLoyalty',
            'Content-Test' => '1234',
        ];

        $this->assertSame($requestHeaders, $defaultRequestHeader->headers());
    }
}
