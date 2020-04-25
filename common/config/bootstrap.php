<?php

$rootPath = dirname(__DIR__, 2);

Yii::setAlias('@root', $rootPath);
Yii::setAlias('@common', $rootPath . '/common');
Yii::setAlias('@console', $rootPath . '/console');
Yii::setAlias('@packages', $rootPath . '/packages');

Yii::setAlias('@apps', $rootPath . '/apps');
Yii::setAlias('@app-backend', $rootPath . '/apps/backend');
Yii::setAlias('@app-frontend', $rootPath . '/apps/frontend');
