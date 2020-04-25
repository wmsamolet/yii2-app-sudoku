<?php
/** @noinspection ParameterDefaultValueIsNotNullInspection */
/** @noinspection SlowArrayOperationsInLoopInspection */

namespace wmsamolet\yii2\tools\helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class FileHelper extends \yii\helpers\FileHelper
{
    public const SIZE_GIGABYTE = 'GB';
    public const SIZE_MEGABYTE = 'MB';
    public const SIZE_KILOBYTE = 'KB';
    public const SIZE_BYTE = 'B';

    public static function size($filePath, $sizeType = self::SIZE_BYTE): float
    {
        return static::formatSize(filesize($filePath), $sizeType);
    }

    public static function formatSize($size, $sizeType = self::SIZE_BYTE): float
    {
        $result = 0;

        switch ($sizeType) {
            case self::SIZE_GIGABYTE:
                $result = $size / 1073741824;
                break;
            case self::SIZE_MEGABYTE:
                $result = $size / 1048576;
                break;
            case self::SIZE_KILOBYTE:
                $result = $size / 1024;
                break;
            case self::SIZE_BYTE:
                $result = $size;
                break;
        }

        return round($result, 2);
    }

    public static function countLines(string $filePath): int
    {
        $countRows = 0;
        $handle = fopen($filePath, 'rb');

        while (!feof($handle) && fgets($handle) !== false) {
            $countRows++;
        }

        fclose($handle);

        return $countRows;
    }

    public static function lines(string $filePath, int $countEndLines = 10): array
    {
        $result = [];
        $lineNumber = 0;
        $countLines = static::countLines($filePath);
        $startLineNumber = $countLines - $countEndLines;
        $startLineNumber = $startLineNumber > 0 ? $startLineNumber : 1;

        $handle = fopen($filePath, 'rb');

        while (!feof($handle) && ($line = fgets($handle)) !== false) {
            $lineNumber++;

            if ($lineNumber >= $startLineNumber) {
                $result[] = preg_replace("/[\n\t]*/", '', $line);
            }
        }

        fclose($handle);

        return $result;
    }

    public static function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, static::rglob($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }

    public static function rsearch($directoryPath, $pattern)
    {
        $directoryIterator = new RecursiveDirectoryIterator($directoryPath);
        $recursiveIteratorIterator = new RecursiveIteratorIterator($directoryIterator);
        $files = new RegexIterator($recursiveIteratorIterator, $pattern, RegexIterator::GET_MATCH);
        $fileList = [];

        foreach ($files as $file) {
            $fileList = array_merge($fileList, $file);
        }

        return $fileList;
    }
}