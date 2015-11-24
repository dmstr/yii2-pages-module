<?php
/**
 * Application configuration for unit tests
 */
return yii\helpers\ArrayHelper::merge(
    require($basePath . '/src/config/main.php'),
    require(__DIR__ . '/config.php'),
    [

    ]
);
