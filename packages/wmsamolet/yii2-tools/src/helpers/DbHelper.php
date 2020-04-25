<?php

namespace wmsamolet\yii2\tools\helpers;

/**
 * Class DbHelper
 */
class DbHelper
{
    /**
     * @param string $name
     * @param string $dsnString
     * @return string
     */
    public static function getDsnAttribute($name, $dsnString): ?string
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsnString, $match)) {
            return $match[1];
        }

        return null;
    }
}