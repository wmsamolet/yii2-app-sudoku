<?php
/** @noinspection PhpUndefinedConstantInspection */
/** @noinspection DuplicatedCode */
/** @noinspection PhpIncludeInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection UsingInclusionReturnValueInspection */

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
    ],
];

$config['bootstrap'][] = 'debug';
$config['modules']['debug'] = [
    'class' => yii\debug\Module::class,
    'allowedIPs' => ['*'],
];

return $config;
