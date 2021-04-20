<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Model;

use Broadway\Serializer\Serializable;

/**
 * Class AccountActivationMethod.
 */
class AccountActivationMethod implements Serializable
{
    const METHOD_EMAIL = 'email';
    const METHOD_SMS = 'sms';

    /**
     * @var array
     */
    protected static $availableMethods = [
        self::METHOD_EMAIL,
        self::METHOD_SMS,
    ];

    /**
     * @var string
     */
    protected $method;

    /**
     * AccountActivationMethod constructor.
     *
     * @param null $method
     */
    public function __construct($method = null)
    {
        $this->setMethod($method);
    }

    /**
     * @param $method
     *
     * @return int
     */
    public static function isMethodEmail($method)
    {
        return 0 === strcasecmp(self::METHOD_EMAIL, $method);
    }

    /**
     * @return AccountActivationMethod
     */
    public static function methodEmail()
    {
        return new static(self::METHOD_EMAIL);
    }

    /**
     * @param $method
     *
     * @return int
     */
    public static function isMethodSms($method)
    {
        return 0 === strcasecmp(self::METHOD_SMS, $method);
    }

    /**
     * @return AccountActivationMethod
     */
    public static function methodSms()
    {
        return new static(self::METHOD_SMS);
    }

    /**
     * @return array
     */
    public static function getAvailableMethods()
    {
        return self::$availableMethods;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method = null)
    {
        if (null !== $method && !in_array($method, self::getAvailableMethods())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Method should be %s',
                    implode(' or ', self::getAvailableMethods())
                )
            );
        }
        $this->method = $method;
    }

    /**
     * @param $methodData
     *
     * @return AccountActivationMethod
     */
    public static function fromData($methodData)
    {
        $methodData = self::resolveOptions($methodData);

        return new self($methodData['method']);
    }

    /**
     * @param $data
     *
     * @return array
     */
    private static function resolveOptions($data)
    {
        $default = [
            'method' => null,
        ];

        return array_merge($default, $data);
    }

    /**
     * @param array $data
     *
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return self::fromData($data);
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'method' => $this->getMethod(),
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getMethod();
    }
}
