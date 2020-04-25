<?php
/** @noinspection ParameterDefaultValueIsNotNullInspection */

namespace wmsamolet\yii2\tools\helpers;

use Throwable;
use Yii;
use yii\helpers\Json;

class JsonHelper extends Json
{
    public static function safeEncode($value, $options = 320)
    {
        try {
            return parent::encode($value, $options);
        } catch (Throwable $exception) {
            Yii::error($exception->getTraceAsString());
        }

        return null;
    }

    public static function safeDecode($json, $asArray = true, $defaultValue = null, $error = false)
    {
        try {
            return parent::decode($json, $asArray);
        } catch (Throwable $exception) {
            if ($error) {
                Yii::error($exception->getTraceAsString());
            }
        }

        return $defaultValue ?? null;
    }

    public static function safeEncodeInline($value)
    {
        return str_replace(
            ["\n", "\r", "\t"],
            ['\\n', '\\r', '\\t'],
            static::safeEncode($value)
        );
    }

    public static function safeDecodeInline($json, $asArray = true)
    {
        return str_replace(
            ['\\n', '\\r', '\\t'],
            ["\n", "\r", "\t"],
            static::safeDecode($json, $asArray)
        );
    }
}