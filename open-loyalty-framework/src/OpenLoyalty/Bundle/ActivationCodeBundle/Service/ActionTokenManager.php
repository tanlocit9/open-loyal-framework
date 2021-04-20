<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Service;

use OpenLoyalty\Bundle\ActivationCodeBundle\Method\ActivationMethod;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ActivationMethodProvider.
 */
class ActionTokenManager
{
    const CACHE_TTL = 60;

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * @var array
     */
    private $availableMethods;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $ac;

    /**
     * @var ActivationCodeManager
     */
    private $activationCodeManager;

    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * ActivationMethodProvider constructor.
     *
     * @param SettingsManager               $settingsManager
     * @param AuthorizationCheckerInterface $ac
     * @param ActivationCodeManager         $activationCodeManager
     * @param AdapterInterface              $cache
     * @param ActivationMethod[]            $availableMethods
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        SettingsManager $settingsManager,
        AuthorizationCheckerInterface $ac,
        ActivationCodeManager $activationCodeManager,
        AdapterInterface $cache,
        array $availableMethods,
        TranslatorInterface $translator
    ) {
        $this->settingsManager = $settingsManager;
        $this->ac = $ac;
        $this->activationCodeManager = $activationCodeManager;
        $this->cache = $cache;
        $this->availableMethods = $availableMethods;
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    public function getCurrentMethod()
    {
        try {
            $item = $this->cache->getItem('account_activation_method');
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            $item = null;
        }
        if ($item && $item->isHit()) {
            return $item->get();
        }

        $accountActivationMethod = $this->settingsManager->getSettingByKey('accountActivationMethod');
        if (!$accountActivationMethod || !$accountActivationMethod->getValue()) {
            throw new \InvalidArgumentException(
                $this->translator->trans('Setting "accountActivationMethod" is not set')
            );
        }

        $value = $accountActivationMethod->getValue();
        if ($item) {
            $item->set($value);
            $item->expiresAfter(self::CACHE_TTL);
            $this->cache->save($item);
        }

        return $value;
    }

    /**
     * @param Customer $user
     */
    public function sendActivationMessage(Customer $user)
    {
        $method = $this->getMethod();
        $fallback = $this->getFallback($method);

        if ($user->getIsActive()) {
            return;
        }

        if ($this->ac->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$method->sendActivationMessage($user) && $fallback) {
            $fallback->sendActivationMessage($user);
        }
    }

    /**
     * @param CustomerDetails $customer
     * @param string          $password
     */
    public function sendTemporaryPassword(CustomerDetails $customer, string $password)
    {
        $method = $this->getMethod();
        $fallback = $this->getFallback($method);

        if (!$method->sendTemporaryPassword($customer, $password) && $fallback) {
            $fallback->sendTemporaryPassword($customer, $password);
        }
    }

    /**
     * @param Customer    $customer
     * @param string|null $token
     */
    public function sendPasswordReset(Customer $customer, string $token = null)
    {
        $method = $this->getMethod();
        $fallback = $this->getFallback($method);

        if (!$method->sendPasswordReset($customer, $token) && $fallback) {
            $fallback->sendPasswordReset($customer, $token);
        }
    }

    /**
     * @return ActivationMethod|null
     */
    private function getMethod()
    {
        $current = $this->getCurrentMethod();

        if (!isset($this->availableMethods[$current])) {
            return $this->getFallback($this->availableMethods);
        }

        return $this->availableMethods[$current];
    }

    /**
     * @param ActivationMethod|null $excluded
     *
     * @return null|ActivationMethod
     */
    private function getFallback(ActivationMethod $excluded = null)
    {
        foreach ($this->availableMethods as $method) {
            if (get_class($method) == get_class($excluded)) {
                continue;
            }
            if ($method->canBeUsed()) {
                return $method;
            }
        }

        return;
    }
}
