<?php

namespace wmsamolet\yii2\modules\sudoku;

use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\console\Application as ConsoleApplication;

class SudokuModule extends Module implements BootstrapInterface
{
    public const T_CATEGORY = 'app';

    public $controllerNamespace = 'wmsamolet\yii2\modules\sudoku\controllers';
    public $defaultRoute = 'sudoku';

    public $accessTokenSalt;

    public function bootstrap($app): void
    {
        if ($app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'wmsamolet\yii2\modules\sudoku\commands';
        }
    }
}