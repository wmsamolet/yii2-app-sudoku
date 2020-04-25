<?php

namespace wmsamolet\yii2\tools\helpers;

use ReflectionClass;
use Throwable;
use function class_exists;
use function preg_match_all;
use function str_replace;

class ExceptionHelper
{
    public static function toArray(Throwable $exception, int $traceLevel = 10): array
    {
        $i = 0;
        $traceArray = [];

        foreach ($exception->getTrace() as $trace) {
            if ($i >= $traceLevel) {
                break;
            }

            $file = $trace['file'] ?? null;
            $line = $trace['line'] ?? null;
            $text = $trace['text'] ?? null;

            $traceArray[] = "#{$i} {$file} ({$line}): {$text}";
            $i++;
        }

        return [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
            'trace' => implode("\n", $traceArray),
        ];
    }

    public static function toString(
        Throwable $exception,
        int $traceLevel = 10,
        string $format = "%s (%s):\n\t\n%s\n\t\n%s",
        bool $render = false
    ): string {
        $string = sprintf(
            ...array_values(
                array_merge(
                    ['format' => $format],
                    static::toArray($exception, $traceLevel)
                )
            )
        );

        return $render ? static::renderTraceString($string) : $string;
    }

    public static function renderTraceString($traceString)
    {
        $filtersQueue = [];
        $filtersQueuePriority = [
            'class',
            'phpstorm',
        ];

        /**
         * {{filter:FILTER_PARAMS}}
         */
        if (preg_match_all('/([\w\-_.:\\\]+)\s+\((\d+)\)\s*:/u', $traceString, $matches, PREG_SET_ORDER)) {

            foreach ($matches as $match) {
                $filterMatch = ArrayHelper::getValue($match, 0);
                $traceFile = ArrayHelper::getValue($match, 1);
                $traceLine = ArrayHelper::getValue($match, 2);
                $filterValue = ['file' => $traceFile, 'line' => $traceLine, 'text' => $traceFile];
                $filterName = 'phpstorm';

                $filtersQueuePriorityValue = array_search($filterName, $filtersQueuePriority);

                if ($filtersQueuePriorityValue !== null) {
                    ArrayHelper::addValue(
                        $filtersQueue,
                        $filtersQueuePriorityValue,
                        [$filterName, $filterValue, $filterMatch]
                    );
                }
            }
        }

        /**
         * {{filter:FILTER_PARAMS}}
         */
        if (preg_match_all('/{{(\w+):([^}]+)}}/u', $traceString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $filterMatch = ArrayHelper::getValue($match, 0);
                $filterName = ArrayHelper::getValue($match, 1);
                $filterValue = ArrayHelper::getValue($match, 2);

                $filtersQueuePriorityValue = array_search($filterName, $filtersQueuePriority, true);

                if ($filtersQueuePriorityValue !== null) {
                    ArrayHelper::addValue(
                        $filtersQueue,
                        $filtersQueuePriorityValue,
                        [$filterName, $filterValue, $filterMatch]
                    );
                }
            }
        }

        /**
         * {{filter}} something... {{/filter}}
         */
        // if (preg_match_all('/{{([^}]+)}}(.*?){{\/[^}]+}}/u', $traceString, $matches, PREG_SET_ORDER)) {
        //     foreach ($matches as $match) {
        //         $filterMatch = ArrayHelper::getValue($match, 0);
        //         $filterName = ArrayHelper::getValue($match, 1);
        //         $filterValue = ArrayHelper::getValue($match, 2);
        //
        //         $filtersQueuePriorityValue = array_search($filterName, $filtersQueuePriority);
        //
        //         if ($filtersQueuePriorityValue !== null) {
        //             ArrayHelper::addValue(
        //                 $filtersQueue,
        //                 $filtersQueuePriorityValue,
        //                 [$filterName, $filterValue, $filterMatch]
        //             );
        //         }
        //     }
        // }

        foreach ($filtersQueue as $filters) {
            foreach ($filters as $filter) {
                [$filterName, $filterValue, $filterMatch] = $filter;

                switch ($filterName) {
                    case 'class':
                        if (class_exists($filterValue)) {
                            $r = new ReflectionClass($filterValue);
                            $classTrace = "<a href=\"phpstorm://open?file={$r->getFileName()}&line={$r->getStartLine()}\">{$filterValue}</a>";

                            $traceString = str_replace(
                                $filterMatch,
                                $classTrace,
                                $traceString
                            );
                        }
                        break;

                    case 'phpstorm':
                        if (is_array($filterValue)) {
                            $trace = $filterValue;
                        } else {
                            parse_str($filterValue, $trace);
                        }

                        $classTrace = "<a href=\"phpstorm://open?file={$trace['file']}&line={$trace['line']}\">{$trace['text']}</a>";

                        $traceString = str_replace(
                            $filterMatch,
                            $classTrace,
                            $traceString
                        );
                        break;
                }
            }
        }

        return $traceString;
    }
}
