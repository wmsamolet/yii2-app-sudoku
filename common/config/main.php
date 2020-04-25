<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'components' => [
        'redis' => [
            'class' => yii\redis\Connection::class,
            'database' => 0,
            'retries' => 1,
        ],
        'cache' => [
            'class' => yii\redis\Cache::class,
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 1,
            ],
        ],
        'session' => [
            'class' => yii\redis\Session::class,
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 2,
            ],
        ],
        'log' => [
            'traceLevel' => 5,
        ],
    ],
    'modules' => [
        'sudoku' => [
            'class' => wmsamolet\yii2\modules\sudoku\SudokuModule::class,
            'accessTokenSalt' => 'qwertyuiop1234567890',
        ],
    ],
];
