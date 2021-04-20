<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\MarkDownBundle\Service;

use JMS\Serializer\Context;

/**
 * Class FOSContextProvider.
 */
class FOSContextProvider implements ContextProvider
{
    /**
     * Output format attribute name.
     */
    const OUTPUT_FORMAT_ATTRIBUTE_NAME = 'output_format';

    /**
     * @var Context
     */
    private $context;

    /**
     * FOSContextProvider constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFormat(): ?string
    {
        $attribute = $this->context->attributes->get(self::OUTPUT_FORMAT_ATTRIBUTE_NAME);

        if (!$attribute->isDefined()) {
            return null;
        }

        return $attribute->get();
    }
}
