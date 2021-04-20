<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\MarkDown\Tests\Unit\Infrastructure;

use OpenLoyalty\Component\MarkDown\Infrastructure\ParseDownParser;
use PHPUnit\Framework\TestCase;

/**
 * Class ParseDownParserTest.
 */
class ParseDownParserTest extends TestCase
{
    /**
     * @test
     */
    public function it_parses_markdown_italic()
    {
        $parser = new ParseDownParser();
        $formattedValue = $parser->parse('*test*');

        $this->assertSame('<em>test</em>', $formattedValue);
    }

    /**
     * @test
     */
    public function it_parses_markdown_bold()
    {
        $parser = new ParseDownParser();
        $formattedValue = $parser->parse('**test**');

        $this->assertSame('<strong>test</strong>', $formattedValue);
    }

    /**
     * @test
     */
    public function it_parses_markdown_link()
    {
        $parser = new ParseDownParser();
        $formattedValue = $parser->parse('[SomeUrl](http://example.com)');

        $this->assertSame('<a href="http://example.com">SomeUrl</a>', $formattedValue);
    }
}
