<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\MarkDownBundle\Service;

use OpenLoyalty\Component\MarkDown\Infrastructure\MarkDownParser;

/**
 * Class ContextMarkDownFormatter.
 */
class ContextMarkDownFormatter implements ContextFormatter
{
    /**
     * Html format.
     */
    const FORMAT_HTML = 'html';

    /**
     * @var MarkDownParser
     */
    protected $markDownParser;

    /**
     * RequestMarkDownFormatter constructor.
     *
     * @param MarkDownParser $markDownParser
     */
    public function __construct(MarkDownParser $markDownParser)
    {
        $this->markDownParser = $markDownParser;
    }

    /**
     * {@inheritdoc}
     */
    public function format(?string $value, ContextProvider $context): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $requestedFormat = $context->getOutputFormat();

        if (self::FORMAT_HTML === $requestedFormat) {
            return $this->markDownParser->parse($value);
        }

        return $value;
    }
}
