<?php

namespace wmsamolet\yii2\tools\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper as YiiArrayHelper;

class ArrayHelper extends YiiArrayHelper
{
    public static function createObject(
        array $objectData,
        string $classKey = '__class',
        string $paramsKey = '__params'
    ) {
        $params = [];

        if (!isset($objectData[$classKey])) {
            throw new InvalidConfigException('Invalid parameter "__class"');
        }

        if (isset($objectData[$paramsKey]) && is_array($objectData[$paramsKey])) {
            $params = $objectData[$paramsKey];

            unset($objectData[$paramsKey]);
        }

        return Yii::createObject($objectData, $params);
    }

    public static function searchColumnValues(&$array, $column, array $values)
    {
        return array_filter($array, function ($el) use ($column, $values) {
            $columnValue = static::getValue($el, $column);

            foreach ($values as $value) {
                if (mb_stripos($columnValue, $value) === false) {
                    return false;
                }
            }

            return true;
        });
    }

    public static function searchColumnArrayValues(&$array, $column, array $searchValues)
    {
        $countSearchValues = count($searchValues);

        return array_filter($array, function ($el) use ($column, $searchValues, $countSearchValues) {
            $columnValues = static::getValue($el, $column);
            $countColumnValues = count($columnValues);

            if (!is_array($columnValues) || $countColumnValues !== $countSearchValues) {
                return false;
            }

            $countFound = 0;

            foreach ($searchValues as $searchValue) {
                foreach ($columnValues as $k => $columnValue) {
                    if ($searchValue !== '' && strpos($columnValue, $searchValue) !== false) {
                        $countFound++;
                    }
                }
            }

            return count($searchValues) === $countFound;
        });
    }

    /**
     * @param array $array
     * @param string|array $path
     * @param mixed $value
     */
    public static function addValue(&$array, $path, $value): void
    {
        if ($path === null) {
            $array = $value;

            return;
        }

        $keys = is_array($path) ? $path : explode('.', $path);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)][] = $value;
    }

    /**
     * @param array|object $array
     * @param string|array $key
     *
     * @return bool
     */
    public static function hasValue($array, $key): bool
    {
        $default = 'ARRAY_VALUE_IS_NOT_EXISTS';

        return static::getValue($array, $key, $default) !== $default;
    }

    /**
     * @param array[][] $arr1
     * @param array[][] $arr2
     *
     * @return array[][]
     */
    public static function diffKeyRecursive(array $arr1, array $arr2): array
    {
        $diff = array_diff_key($arr1, $arr2);
        $intersect = array_intersect_key($arr1, $arr2);

        foreach ($intersect as $k => $v) {
            if (is_array($arr1[$k]) && is_array($arr2[$k])) {
                $d = static::diffKeyRecursive($arr1[$k], $arr2[$k]);

                if ($d) {
                    $diff[$k] = $d;
                }
            }
        }

        return $diff;
    }

    /**
     * @param array[][] $arr1
     * @param array[][] $arr2
     *
     * @return array[][]
     */
    public static function diffRecursive(array $arr1, array $arr2): array
    {
        $result = [];

        foreach ($arr1 as $key => $value) {
            if (array_key_exists($key, $arr2)) {
                if (is_array($value)) {
                    $diff = static::diffRecursive($value, $arr2[$key]);
                    if (count($diff)) {
                        $result[$key] = $diff;
                    }
                } elseif ($value !== $arr2[$key]) {
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Convert array to CSS string
     *
     * @param string[] $cssArray
     *
     * @return string
     */
    public static function toCss(array $cssArray): string
    {
        return implode(
            ' ',
            array_map(
                function ($k, $v) {
                    return $k . ':' . $v . ';';
                },
                array_keys($cssArray), $cssArray
            )
        );
    }
}