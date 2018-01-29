<?php

use yii\db\Migration;

class m161029_011345_settings extends Migration
{
    public function up()
    {
        if (Yii::$app->has('settings')) {
            if (!Yii::$app->settings->get('availableRoutes', 'pages', false)) {
                Yii::$app->settings->set('availableRoutes', '/pages/default/page', 'pages', 'string');
            }
            if (!Yii::$app->settings->get('availableGlobalRoutes', 'pages', false)) {
                Yii::$app->settings->set('availableGlobalRoutes', '/pages/default', 'pages', 'string');
            }
            if (!Yii::$app->settings->get('availableViews', 'pages', false)) {
                Yii::$app->settings->set('availableViews',
                    '@vendor/dmstr/yii2-pages-module/example-views/column1.php',
                    'pages', 'string');
            }
        }
        return true;
    }

    public function down()
    {
        if (Yii::$app->has('settings')) {
            Yii::$app->settings->delete('availableRoutes', 'pages');
            Yii::$app->settings->delete('availableGlobalRoutes', 'pages');
            Yii::$app->settings->delete('availableViews', 'pages');
        }
        return true;
    }

}
