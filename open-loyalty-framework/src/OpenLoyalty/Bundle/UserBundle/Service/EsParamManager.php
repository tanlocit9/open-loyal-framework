<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Service;

/**
 * Class EsParamManager.
 */
class EsParamManager implements ParamManager
{
    public function stripNulls(array $params, $toLower = true, $escape = true, array $types = [])
    {
        foreach ($params as $key => $val) {
            if ($val === null || $val == 'null') {
                unset($params[$key]);
                continue;
            }

            if (is_array($val)) {
                $this->stripNulls($val, $toLower, $escape, $types);
                continue;
            }

            $val = rawurldecode($val);
            $params[$key] = $val;

            if ($toLower) {
                $params[$key] = strtolower($val);
            }
            $newKey = str_replace('_', '.', $key);
            if ($newKey != $key) {
                $params[$newKey] = $params[$key];
                unset($params[$key]);
                $key = $newKey;
            }
            if ($escape) {
                $params[$key] = static::escapeString($params[$key]);
            }
            if (isset($types[$key])) {
                $params[$key] = [
                    'type' => $types[$key],
                    'value' => $params[$key],
                ];
            }
        }

        return $params;
    }

    /**
     * @param array       $params
     * @param string      $key
     * @param null|string $dateFrom
     * @param null|string $dateTo
     */
    public function appendDateRangeFilter(array &$params, string $key, ?string $dateFrom, ?string $dateTo)
    {
        if (!$dateFrom && !$dateTo) {
            return;
        }

        $dateFromTimestamp = strtotime(stripslashes($dateFrom));
        $dateToTimestamp = strtotime(stripslashes($dateTo));

        if (!is_null($dateFrom) && !$dateFromTimestamp) {
            return;
        }

        if (!is_null($dateTo) && !$dateToTimestamp) {
            return;
        }

        $param = [
            'type' => 'range',
            'value' => [],
        ];

        if (!is_null($dateFrom)) {
            $param['value']['gte'] = $dateFromTimestamp;
        }

        if (!is_null($dateTo)) {
            $param['value']['lte'] = $dateToTimestamp;
        }

        $params[$key] = $param;
    }

    protected function escapeString($string)
    {
        $chars = array('\\',  '/', '+', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', "'", '~', '?', ':');
        foreach ($chars as $ch) {
            $string = str_replace($ch, '\\'.$ch, $string);
        }
        $string = str_replace('/', '\/', $string);

        return $string;
    }
}
