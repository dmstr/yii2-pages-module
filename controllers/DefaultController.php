<?php
/**
 * @link http://www.diemeisterei.de/
 *
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\controllers;

use dmstr\modules\backend\interfaces\ContextMenuItemsInterface;
use dmstr\modules\pages\assets\PagesBackendAsset;
use dmstr\modules\pages\helpers\PageHelper;
use dmstr\modules\pages\models\Tree;
use Yii;
use yii\base\Event;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\View;

/**
 * Class DefaultController
 *
 * @package dmstr\modules\pages\controllers
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 *
 * @property array $menuItems
 */
class DefaultController extends Controller implements ContextMenuItemsInterface
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (\Yii::$app->user->can('pages', ['route' => true])) {
            \Yii::$app->trigger('registerMenuItems', new Event(['sender' => $this]));
        }

        parent::init();
    }

    /**
     * @param null $pageId
     *
     * @return mixed
     */
    public function actionIndex($pageId = null)
    {
        $localicedRootNode = $this->module->getLocalizedRootNode();
        if (!$localicedRootNode) {
            $language = mb_strtolower(\Yii::$app->language);
            $rootNodePrefix = Tree::ROOT_NODE_PREFIX;

            $msg = <<<HTML
<b>Localized root-node missing</b>
<p>
Please create a new root-node for the current language.
</p>
<p>
<a onclick="$('#tree-domain_id').val('{$rootNodePrefix}');$('#tree-name').val('{$rootNodePrefix}_{$language}');$('.kv-detail-container button[type=submit]').click()" 
   class="btn btn-warning">Create root-node for <b>{$language}</b></a>
</p>
HTML;

            $js = <<<'JS'
$(".kv-create-root").click();
JS;

            $this->getView()->registerJs($js, View::POS_LOAD);
            \Yii::$app->session->addFlash('warning', $msg);
        } else {
            if (!empty($pageId)) {
                Yii::$app->session->set('kvNodeId', $pageId);
            }
        }

        /**
         * Register the pages asset bundle
         */
        PagesBackendAsset::register($this->view);

        /** @var Tree $queryTree */
        $queryTree = Tree::find()
            ->andWhere(
                [
                    Tree::ATTR_ACCESS_DOMAIN => [
                        \Yii::$app->language,
                        Tree::GLOBAL_ACCESS_DOMAIN
                    ]
                ]
            )
            ->orderBy('root, lft');

        return $this->render('index', ['queryTree' => $queryTree]);
    }

    /**
     * @return \yii\web\Response
     * @throws MethodNotAllowedHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionResolveRouteToSchema()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->post('value') !== null) {
            $route = Yii::$app->request->post('value');

            $response['schema'] = PageHelper::routeToSchema($route);
            return $this->asJson($response);
        }
        throw new MethodNotAllowedHttpException(Yii::t('pages', 'You are not allowed to access this page like this'));
    }

    /**
     * @return array
     */
    public function getMenuItems()
    {
        return [
            [
                'label' => Yii::t('pages', 'Edit page'),
                'url' => ['/' . $this->module->id . '/default/index', 'pageId' => Yii::$app->request->get('pageId')]

            ]
        ];
    }
}
