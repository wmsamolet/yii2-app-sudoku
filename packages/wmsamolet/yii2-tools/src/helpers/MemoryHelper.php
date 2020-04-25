<?php
/** @noinspection ParameterDefaultValueIsNotNullInspection */

namespace wmsamolet\yii2\tools\helpers;

class MemoryHelper
{
    public const SIZE_GIGABYTE = 'GB';
    public const SIZE_MEGABYTE = 'MB';
    public const SIZE_KILOBYTE = 'KB';
    public const SIZE_BYTE = 'B';

    public static function formatSize($size, $sizeType = self::SIZE_BYTE)
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

    public static function meminfo(): array
    {
        $data = explode("\n", file_get_contents('/proc/meminfo'));

        $meminfo = [];

        foreach ($data as $line) {
            [$key, $val] = array_map('trim', explode(':', $line));

            $meminfo[$key] = $val;
        }

        return $meminfo;
    }
}