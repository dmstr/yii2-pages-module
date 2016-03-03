<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2016 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\controllers;


use dmstr\modules\pages\models\Tree;
use yii\filters\AccessControl;
use yii\web\Controller;

class TestController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return \Yii::$app->user->can(
                                $this->module->id.'_'.$this->id.'_'.$action->id,
                                ['route' => true]
                            );
                        },
                    ]
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $tree = Tree::getMenuItems('root_'.\Yii::$app->language);
        return $this->render('index', ['tree' => $tree]);
    }
}