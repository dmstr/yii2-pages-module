<?php

namespace dmstr\modules\pages\controllers;

use dmstr\modules\pages\models\Tree;

class DefaultController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
