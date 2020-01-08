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
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\View;

/**
 * Class DefaultController
 * @package dmstr\modules\pages\controllers
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
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
     * renders a page view from the database.
     *
     * @param $pageId
     *
     * @return string
     *
     * @throws HttpException
     */
    public function actionPage($pageId)
    {
        Url::remember();
        \Yii::$app->session['__crudReturnUrl'] = null;

        // Set layout
        $this->layout = $this->module->defaultPageLayout;

        // deactivate access_* check in ActiveRecordAccessTrait::find to be able to handle 'forbidden pages' here.
        Tree::$activeAccessTrait = false;

        // Get active Tree object, allow access to invisible pages
        // @todo: improve handling, using also roles
        $pageQuery = Tree::find()->andWhere(
            [
                Tree::ATTR_ID => $pageId,
                Tree::ATTR_ACTIVE => Tree::ACTIVE,
            ]
        );

        // get page
        /** @var $page Tree */
        $page = $pageQuery->one();

        // Show disabled pages for admins
        if ($page !== null && $page->isDisabled() && !\Yii::$app->user->can('pages')) {
            $page = null;
        }

        # reactivate access_* check in ActiveRecordAccessTrait::find for further queries
        Tree::$activeAccessTrait = true;
        // check if page has access_read permissions set, if yes check if user is allowed
        if (!empty($page->access_read) && $page->access_read !== '*') {
            if (!\Yii::$app->user->can($page->access_read)) {
                # if userIsGuest, redirect to login page
                if (!\Yii::$app->user->isGuest) {
                    throw new HttpException(403, \Yii::t('pages', 'Forbidden'));
                }

                return $this->redirect(\Yii::$app->user->loginUrl);
            }
        }

        if ($page !== null) {
            // Set page title, use name as fallback
            $this->view->title = $page->page_title ?: $page->name;

            // Register default SEO meta tags
            if (!empty($page->default_meta_keywords)) {
                $this->view->registerMetaTag(['name' => 'keywords', 'content' => $page->default_meta_keywords],'meta-keywords');
            }

            if (!empty($page->default_meta_description)) {
                $this->view->registerMetaTag(['name' => 'description', 'content' => $page->default_meta_description], 'meta-description');
            }

            // Render view
            return $this->render($page->view, ['page' => $page]);
        } else {
            if ($fallbackPage = $this->resolveFallbackPage($pageId)) {
                \Yii::trace('Resolved fallback URL for ' . $fallbackPage->id, __METHOD__);
                return $this->redirect($fallbackPage->createUrl(['language' => $fallbackPage->access_domain]));
            } else {
                throw new HttpException(404, \Yii::t('pages', 'Page not found.') . ' [ID: ' . $pageId . ']');
            }
        }

        if ($fallbackPage = $this->resolveFallbackPage($pageId)) {
            \Yii::trace('Resolved fallback URL for ' . $fallbackPage->id, __METHOD__);
            return $this->redirect($fallbackPage->createUrl(['language' => $fallbackPage->access_domain]));
        }

        throw new HttpException(404, \Yii::t('pages', 'Page not found.') . ' [ID: ' . $pageId . ']');
    }


    /**
     * @param $pageId
     * @return Tree|bool
     */
    private function resolveFallbackPage($pageId)
    {
        $original = Tree::find()->where(['id' => $pageId])->one();

        if (empty($original)) {
            return false;
        }
        return Tree::find()->andWhere(['domain_id' => $original->domain_id])->one();
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
