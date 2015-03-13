<?php

namespace dmstr\modules\pages\controllers;

use yii\filters\AccessControl;

/**
 * Class DefaultController
 * @package dmstr\modules\pages\controllers
 * @author $Author
 */
class DefaultController extends \yii\web\Controller
{
    /**
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = false;

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
                        'allow'   => true,
                        'actions' => ['index', 'page'],
                        'roles'   => ['@']
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            return true;
        } else {
            return false;
        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionPage()
    {
        return $this->render('page');
    }

}
