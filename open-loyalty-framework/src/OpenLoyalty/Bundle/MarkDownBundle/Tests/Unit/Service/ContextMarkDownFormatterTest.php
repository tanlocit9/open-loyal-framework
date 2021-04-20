<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\MarkDownBundle\Tests\Unit\Service;

use OpenLoyalty\Bundle\MarkDownBundle\Service\ContextMarkDownFormatter;
use OpenLoyalty\Bundle\MarkDownBundle\Service\ContextProvider;
use OpenLoyalty\Component\MarkDown\Infrastructure\MarkDownParser;
use OpenLoyalty\Component\MarkDown\Infrastructure\ParseDownParser;
use PHPUnit\Framework\TestCase;

/**
 * Class ContextMarkDownFormatterTest.
 */
class ContextMarkDownFormatterTest extends TestCase
{
    /**
     * @test
     */
    public function it_parses_markdown_if_format_parameter_is_passed()
    {
        $context = $this->getMockBuilder(ContextProvider::class)->getMock();
        $context
            ->expects($this->once())
            ->method('getOutputFormat')
            ->willReturn(ContextMarkDownFormatter::FORMAT_HTML);

        $formatter = new ContextMarkDownFormatter(new ParseDownParser());
        $parsed = $formatter->format('_test_', $context);

        $this->assertSame('<em>test</em>', $parsed);
    }

    /**
     * @test
     */
    public function it_does_not_parse_markdown_if_format_parameter_is_not_passed()
    {
        $context = $this->getMockBuilder(ContextProvider::class)->getMock();
        $context
            ->expects($this->once())
            ->method('getOutputFormat')
            ->willReturn(null);

        $formatter = new ContextMarkDownFormatter(new ParseDownParser());
        $parsed = $formatter->format('_test_', $context);

        $this->assertSame('_test_', $parsed);
    }

    /**
     * @test
     */
    public function it_does_not_parse_markdown_if_null_is_passed()
    {
        $context = $this->getMockBuilder(ContextProvider::class)->getMock();
        $context
            ->expects($this->never())
            ->method('getOutputFormat');

        $markDownParser = $this->getMockBuilder(MarkDownParser::class)->getMock();
        $markDownParser->expects($this->never())->method('parse');

        $formatter = new ContextMarkDownFormatter($markDownParser);
        $formatter->format(null, $context);
    }
}
