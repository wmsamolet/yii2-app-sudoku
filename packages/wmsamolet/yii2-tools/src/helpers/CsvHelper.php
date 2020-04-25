<?php
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpTooManyParametersInspection */
/** @noinspection ParameterDefaultValueIsNotNullInspection */

namespace wmsamolet\yii2\tools\helpers;

class CsvHelper
{
    public static function fileToArray(
        string $filePath,
        $firstRowToKey = false,
        $length = 0,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\'
    ): array {
        $result = [];
        $handle = fopen($filePath, 'rb');
        $params = [$handle, $length, $delimiter, $enclosure, $escape];
        $keys = $firstRowToKey ? fgetcsv(...$params) : [];

        while (!feof($handle) && ($row = fgetcsv(...$params))) {
            $colData = [];

            foreach ($row as $key => $value) {
                $colData[$keys[$key] ?? $key] = $value;
            }

            $result[] = $colData;
        }

        return $result;
    }

    public static function stringToArray(
        string $string,
        $firstRowToKey = false,
        $length = 0,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\'
    ): array {
        $tempFile = tempnam(sys_get_temp_dir(), 'tmp-csv');

        file_put_contents($tempFile, $string);

        return static::fileToArray(
            $tempFile,
            $firstRowToKey,
            $length,
            $delimiter,
            $enclosure,
            $escape
        );
    }

    /**
     * Array to csv
     *
     * @param $rowArray
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     *
     * @return string
     */
    public static function arrayToRowString(
        $rowArray,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\'
    ): string {
        $rowArray = array_map(function ($value) {
            $value = is_scalar($value) ? $value : json_encode($value);
            $value = str_replace("\n", '\\\\n', $value);

            return $value;
        }, $rowArray);

        $fh = fopen('php://temp', 'rwb');

        fputcsv($fh, $rowArray, $delimiter, $enclosure, $escape);

        rewind($fh);

        $string = stream_get_contents($fh);

        fclose($fh);

        return $string;
    }
}