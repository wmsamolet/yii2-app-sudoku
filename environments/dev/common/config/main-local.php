<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */

return [
    'components' => [
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => yii\swiftmailer\Mailer::class,
            'viewPath' => '@common/mail',
            'fileTransportPath' => '@common/runtime/emails',
            'fileTransportCallback' => function (\yii\swiftmailer\Mailer $that) {
                $path = rtrim(Yii::getAlias($that->fileTransportPath), '/');
                $dir = date('Y/m/d/H_i');

                \yii\helpers\FileHelper::createDirectory($path . '/' . $dir, 0777);

                return $dir . '/' . $that->generateMessageFileName();
            },

            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => '127.0.0.1',
                'username' => '',
                'password' => '',
                'port' => 1025,
                'encryption' => false,
            ],
        ],
    ],
];
