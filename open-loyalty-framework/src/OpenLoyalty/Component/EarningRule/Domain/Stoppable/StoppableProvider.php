<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Stoppable;

use OpenLoyalty\Component\EarningRule\Domain\EarningRule;

/**
 * Class StoppableProvider.
 */
class StoppableProvider
{
    /**
     * @param EarningRule $earningRule
     *
     * @return bool
     */
    public function isStoppable(EarningRule $earningRule): bool
    {
        if ($earningRule instanceof StoppableInterface) {
            return true;
        }

        return false;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isStoppableByType(string $type): bool
    {
        $className = EarningRule::TYPE_MAP[$type];

        if (in_array($className, $this->getStoppableClassNames(), true)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getStoppableClassNames(): array
    {
        $stoppable = [];

        foreach (EarningRule::TYPE_MAP as $className) {
            /** @var \ReflectionClass $reflection */
            $reflection = new \ReflectionClass($className);
            if ($reflection->implementsInterface(StoppableInterface::class)) {
                $stoppable[] = $className;
            }
        }

        return $stoppable;
    }
}
