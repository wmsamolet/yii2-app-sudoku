<?php

namespace wmsamolet\yii2\modules\sudoku\assets;

use yii\web\AssetBundle;

class VueSudokuAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/vue/components/sudoku/dist';

    public $depends = [
        VueAsset::class,
    ];

    public $js = [
        YII_ENV_DEV ? 'Sudoku.umd.js' : 'Sudoku.umd.min.js',
    ];

    public $css = [
        'Sudoku.css',
    ];
}