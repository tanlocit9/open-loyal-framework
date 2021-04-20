<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Service;

use OpenLoyalty\Component\EarningRule\Domain\Algorithm\EarningRuleAlgorithmFactoryInterface;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\EarningRuleAlgorithmInterface;

/**
 * Class EarningRuleAlgorithmFactory.
 */
class EarningRuleAlgorithmFactory implements EarningRuleAlgorithmFactoryInterface
{
    private $algorithms;

    /**
     * EarningRuleAlgorithmFactory constructor.
     */
    public function __construct()
    {
        $this->algorithms = [];
    }

    /**
     * @param EarningRuleAlgorithmInterface $algorithm
     * @param string                        $alias
     */
    public function addAlgorithm(EarningRuleAlgorithmInterface $algorithm, $alias)
    {
        $this->algorithms[$alias] = $algorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlgorithm($class)
    {
        $alias = $this->getAliasByClass($class);

        if (array_key_exists($alias, $this->algorithms)) {
            return $this->algorithms[$alias];
        }

        throw new \InvalidArgumentException(
            sprintf('Rule algorithm for class alias %s is not defined.', $alias)
        );
    }

    /**
     * @param $class
     *
     * @return string
     */
    protected function getAliasByClass($class)
    {
        $reflObject = new \ReflectionClass($class);

        return $reflObject->getShortName();
    }
}
