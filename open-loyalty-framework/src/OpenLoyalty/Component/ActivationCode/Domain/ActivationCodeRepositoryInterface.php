<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\ActivationCode\Domain;

/**
 * Interface ActivationCodeRepositoryInterface.
 */
interface ActivationCodeRepositoryInterface
{
    /**
     * @return array
     */
    public function getAll();

    /**
     * Save activation code.
     *
     * @param ActivationCode $activationCode
     */
    public function save(ActivationCode $activationCode);

    /**
     * Get activation code by id.
     *
     * @param ActivationCodeId $activationCodeId
     *
     * @return null|ActivationCode
     */
    public function getById(ActivationCodeId $activationCodeId);

    /**
     * @param string $objectType
     * @param string $objectId
     *
     * @return int
     */
    public function countByObjectTypeAndObjectId(string $objectType, string $objectId);

    /**
     * @param string $objectType
     * @param string $objectId
     *
     * @return null|ActivationCode
     */
    public function getLastByObjectTypeAndObjectId(string $objectType, string $objectId);

    /**
     * Get activation code by code.
     *
     * @param string $code
     *
     * @return null|ActivationCode
     */
    public function getByCode($code);
}
