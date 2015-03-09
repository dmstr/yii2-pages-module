<?php

namespace dmstr\modules\pages\controllers;

use dmstr\modules\pages\models\Tree;

class DefaultController extends \yii\web\Controller
{
    public function actionIndex()
    {
        if (Tree::find()->count() == 0) {
            $home = new Tree(['name' => 'Home']);
            $home->makeRoot();
            $site1 = new Tree(['name' => 'Site 1']);
            $site1->prependTo($home);
        }

        return $this->render('index');
    }

}
