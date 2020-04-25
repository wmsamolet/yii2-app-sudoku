<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace common\bootstrap;

use Yii;
use yii\base\BootstrapInterface;

class DIContainer implements BootstrapInterface
{
    /** @inheritdoc */
    public function bootstrap($app): void
    {
        Yii::$container->setSingletons([
            \Wmsamolet\PhpWebsocket\ServerInterface::class => function () {
                return new \Wmsamolet\PhpWebsocket\Ratchet\RatchetServer(
                    new \Wmsamolet\PhpWebsocket\Ratchet\RatchetMessageComponent(),
                    ['port' => 9090]
                );
            },
        ]);
    }
}
