<?php

namespace wmsamolet\yii2\tools\helpers;

use Throwable;
use yii\helpers\StringHelper as YiiStringHelper;
use yii\helpers\VarDumper;

class StringHelper extends YiiStringHelper
{
    public static function renderPattern(?string $string, array $params = [])
    {
        if (preg_match_all('/{{([^}]+)}}/u', $string, $patternMatches, PREG_SET_ORDER)) {
            foreach ($patternMatches as $patternMatch) {
                [$variableMatch, $varKey] = $patternMatch;

                $varKey = trim($varKey);
                $varKeyFilters = explode('|', $varKey);
                $varKey = trim(array_shift($varKeyFilters));

                try {
                    $varValue = ArrayHelper::getValue($params, $varKey);
                } catch (Throwable $exception) {
                    $varValue = "[getValue exception: {$exception->getMessage()}]";
                }

                foreach ($varKeyFilters as $varKeyFilter) {
                    if (preg_match('/^([^\[{]+)/u', $varKeyFilter, $filterNameMatches)) {
                        $filterName = trim($filterNameMatches[1]);
                        $filterParams = trim(str_replace($filterNameMatches[1], '', $varKeyFilter));
                    } else {
                        continue;
                    }

                    $decodedFilterParams = [];

                    try {
                        $filterParams = JsonHelper::decode($filterParams);

                        if (is_array($filterParams)) {
                            foreach ($filterParams as $paramName => $paramValue) {
                                if (is_string($paramValue) && in_array(trim($paramValue), ['$$', '$VAR', '[[VAR]]'])) {
                                    $paramValue = $varValue;
                                }

                                $decodedFilterParams[trim($paramName)] = $paramValue;
                            }
                        }
                    } catch (Throwable $exception) {
                        $varValue = "[decodedFilterParams exception: {$exception->getMessage()}, filterParams: {$filterParams}]";
                    }

                    if (!count($decodedFilterParams)) {
                        $decodedFilterParams[] = $varValue;
                    }

                    try {
                        $varValue = call_user_func_array($filterName, $decodedFilterParams);
                    } catch (Throwable $exception) {
                        $dump = VarDumper::dumpAsString($decodedFilterParams);
                        $varValue .= " [filter '{$filterName}' exception: {$exception->getMessage()} dump: {$dump}]";
                    }
                }

                if (!is_scalar($varValue)) {
                    $varValue = '[var:' . $varKey . ', type:' . gettype($varValue) . ', data:' . VarDumper::dumpAsString($varValue) . ']';
                }

                $string = str_replace($variableMatch, $varValue, $string);
            }
        }

        return $string;
    }
}