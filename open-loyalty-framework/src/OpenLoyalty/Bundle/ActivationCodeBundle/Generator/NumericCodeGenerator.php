<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Generator;

/**
 * Class NumericCodeGenerator.
 */
class NumericCodeGenerator implements CodeGenerator
{
    /**
     * Type.
     */
    const TYPE = 'num';

    /**
     * @var AlphaNumericCodeGenerator
     */
    protected $alphaNumCodeGenerator;

    /**
     * NumericCodeGenerator constructor.
     *
     * @param AlphaNumericCodeGenerator $alphaNumCodeGenerator
     */
    public function __construct(AlphaNumericCodeGenerator $alphaNumCodeGenerator)
    {
        $this->alphaNumCodeGenerator = $alphaNumCodeGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $objectType, string $objectId, int $length)
    {
        $hash = $this->alphaNumCodeGenerator->generate($objectType, $objectId, 0);
        $hash = preg_replace('/[^0-9,.]/', '', $hash);

        $hash = (int) substr($hash, 0, $length);

        // prevent a situation where a hash is shorter than expected
        while (strlen($hash) < $length) {
            $hash = $hash.uniqid(mt_rand(), true).microtime(true);
            $hash = (int) substr($hash, 0, $length);
        }

        return $hash;
    }
}
