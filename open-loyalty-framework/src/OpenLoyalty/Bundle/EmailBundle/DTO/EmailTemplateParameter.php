<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\EmailBundle\DTO;

/**
 * Class EmailTemplateParameter.
 */
class EmailTemplateParameter
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $parameterBag = [];

    /**
     * EmailTemplateParameter constructor.
     *
     * @param string $templateName
     * @param array  $parameterBag
     */
    public function __construct(string $templateName, array $parameterBag = [])
    {
        $this->template = $templateName;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param string      $name
     * @param null|string $value
     */
    public function addParameter(string $name, ?string $value): void
    {
        $this->parameterBag[$name] = $value;
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function parameters(): array
    {
        return $this->parameterBag;
    }
}
