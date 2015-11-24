<?php
/**
 * Application configuration for acceptance tests
 */
$config = yii\helpers\ArrayHelper::merge(
    require('/app/src/config/main.php'),
    require(__DIR__ . '/../_config/config.php'),
    [
        'controllerNamespace' => 'app\controllers',
    ]
);

new yii\web\Application($config);
