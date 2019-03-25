<?php

namespace dmstr\modules\pages\controllers\api;

use dmstr\modules\pages\models\Tree;
use yii\rest\IndexAction;

/**
 * This is the class for REST controller "DefaultController".
 *
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class DefaultController extends \yii\rest\ActiveController
{
    /**
     * The limit for the \yii\data\ActiveDataProvider.
     */
    const QUERY_LIMIT = 2000;

    public $modelClass = Tree::class;

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            /*
             * Supported $_GET params for /pages/api/default/index
             *
             * @param dmstr\modules\pages\models\Tree::ATTR_ID
             * @param dmstr\modules\pages\models\Tree::ATTR_DOMAIN_ID
             * @param dmstr\modules\pages\models\Tree::ATTR_ROOT
             * @param dmstr\modules\pages\models\Tree::ATTR_ACCESS_DOMAIN
             */
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function () {

                    /* @var $modelClass \yii\db\BaseActiveRecord */
                    $modelClass = $this->modelClass;

                    $query = $modelClass::find();

                    if (isset($_GET[$modelClass::ATTR_ID])) {
                        $query->andFilterWhere([$modelClass::ATTR_ID => $_GET[$modelClass::ATTR_ID]]);
                    }
                    if (isset($_GET[$modelClass::ATTR_DOMAIN_ID])) {
                        $query->andFilterWhere([$modelClass::ATTR_DOMAIN_ID => $_GET[$modelClass::ATTR_DOMAIN_ID]]);
                    }
                    if (isset($_GET[$modelClass::ATTR_ACCESS_DOMAIN])) {
                        $query->andFilterWhere([$modelClass::ATTR_ACCESS_DOMAIN => $_GET[$modelClass::ATTR_ACCESS_DOMAIN]]);
                    }
                    if (isset($_GET[$modelClass::ATTR_ROOT])) {
                        $query->andFilterWhere([$modelClass::ATTR_ROOT => $_GET[$modelClass::ATTR_ROOT]]);
                    }

                    return new \yii\data\ActiveDataProvider(
                        [
                            'query' => $query,
                            'pagination' => [
                                'pageSize' => self::QUERY_LIMIT,
                            ],
                        ]
                    );
                },
            ],
        ];
    }
}
