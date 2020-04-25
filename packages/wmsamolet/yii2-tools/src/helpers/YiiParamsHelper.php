<?php

namespace wmsamolet\yii2\tools\helpers;

use Yii;

class YiiParamsHelper
{
    public static function get($key, $defaultValue = null)
    {
        return ArrayHelper::getValue(Yii::$app->params, $key, $defaultValue);
    }
}