<?php

namespace wmsamolet\yii2\modules\sudoku\assets;

use yii\web\AssetBundle;

class VueAsset extends AssetBundle
{
    public $sourcePath = '@npm/vue/dist';

    public $js = [
        YII_ENV_DEV ? 'vue.js' : 'vue.min.js',
    ];
}