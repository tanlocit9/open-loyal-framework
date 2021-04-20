<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Generator;

/**
 * Class AlphaNumericCodeGenerator.
 */
class AlphaNumericCodeGenerator implements CodeGenerator
{
    /**
     * Type.
     */
    const TYPE = 'alphanum';

    /**
     * {@inheritdoc}
     */
    public function generate(string $objectType, string $objectId, int $length)
    {
        $hash = hash('sha512', implode('', [
            uniqid(mt_rand(), true),
            microtime(true),
            bin2hex(openssl_random_pseudo_bytes(100)),
            $objectType,
            $objectId,
        ]));

        if ($length > 0) {
            $hash = substr($hash, mt_rand(0, strlen($hash) - $length - 1), $length);
        }

        return strtoupper($hash);
    }
}
