<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Form\DataTransformer;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class LabelsDataTransformer.
 */
class LabelsDataTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @var string
     */
    protected $labelDelimiter = ':';

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value == null) {
            return;
        }
        if (!is_array($value)) {
            throw new \InvalidArgumentException();
        }
        $values = array_map(function (Label $label) {
            return $label->getKey().$this->labelDelimiter.$label->getValue();
        }, $value);

        return implode($this->delimiter, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        $values = explode($this->delimiter, $value);
        $transformed = array_map(function ($code) {
            if (!$code) {
                return;
            }

            $value = explode($this->labelDelimiter, $code);

            return new Label($value[0], $value[1]);
        }, $values);

        $transformed = array_filter($transformed, function ($element) {
            if ($element == null) {
                return false;
            }

            return true;
        });

        return $transformed;
    }
}
