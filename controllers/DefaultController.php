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

use dmstr\activeRecordPermissions\ActiveRecordAccessTrait;
use dmstr\modules\backend\interfaces\ContextMenuItemsInterface;
use dmstr\modules\pages\assets\PagesBackendAsset;
use dmstr\modules\pages\helpers\PageHelper;
use dmstr\modules\pages\models\Tree;
use dmstr\modules\pages\Module;
use dmstr\modules\pages\traits\RequestParamActionTrait;
use kartik\tree\TreeView;
use pheme\settings\components\Settings;
use Yii;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\View;

/**
 * Class DefaultController
 * @package dmstr\modules\pages\controllers
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class DefaultController extends Controller implements ContextMenuItemsInterface
{

    use RequestParamActionTrait;

    /**
     * ignore pageId param as req-param for actionPage as the id is provided from model->id itself
     * required as we use RequestParamActionTrait in this controller
     *
     * @return false
     */
    protected function pageActionParamPageId()
    {
        return false;
    }

    /**
     * pageId param provider for actionRefPage()
     * pages are fetched from defined rootIds
     *
     * @return array
     */
    protected function refPageActionParamPageId()
    {

        $rootIds = [Tree::ROOT_NODE_PREFIX];
        /** @var Settings Yii::$app->settings */
        if (Module::checkSettingsInstalled() && Yii::$app->settings->get('refPageRootIds', 'pages', null)) {
            $tmp = explode("\n", Yii::$app->settings->get('pages.refPageRootIds'));
            $tmp = array_filter(array_map('trim', $tmp));
            $rootIds = $tmp ?? $rootIds;
        }

        $pages = [];
        foreach ($rootIds as $rootId) {
            $rootNode = Tree::getRootByDomainId($rootId);
            if ($rootNode) {
                $leaves = Tree::getLeavesFromRoot($rootNode)->andWhere(['route' => Tree::DEFAULT_PAGE_ROUTE])->all();
                if (!empty($leaves)) {
                    $leaves = array_filter($leaves, function($leave) {
                        if (!empty($leave->request_params)) {
                            $params = Json::decode($leave->request_params);
                            if (!empty($params) && isset($params->pageId) && $params->pageId == $leave->id) {
                                return false;
                            }
                        }
                        return true;
                    });
                    /** @var Tree $leave */
                    foreach ($leaves as $leave) {
                        Yii::debug(ArrayHelper::map($leave->parents()->all(), 'id', 'name'));
                        if (!$leave->isPage()) {
                            continue;
                        }
                        // build human-readable label for each leave
                        $pages[$leave->id] = Html::encode(implode(' :: ', ArrayHelper::merge(ArrayHelper::map($leave->parents()->all(), 'id', 'name'), [$leave->name . ' (' . $leave->id . ')'])));
                    }
                }
            }
        }

        $params = ArrayHelper::merge(['' => Html::encode(Yii::t('pages', 'Select target page'))], $pages);
        return $params;

    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->user->can('pages', ['route' => true])) {
            Yii::$app->trigger('registerMenuItems', new Event(['sender' => $this]));
        }

        parent::init();
    }

    /**
     * @return mixed
     */
    public function actionIndex($pageId = null)
    {
        $queryTree = Tree::find()
            ->andWhere(
                [
                    Tree::ATTR_ACCESS_DOMAIN => [
                        Yii::$app->language,
                        Tree::GLOBAL_ACCESS_DOMAIN
                    ]
                ]
            )
            ->orderBy('root, lft');

        $headerTemplate = <<< HTML
<div class="row">
    <div class="col-sm-6" id="dmstr-pages-detail-heading">
        {heading}
    </div>
    <div class="col-sm-6" id="dmstr-pages-detail-search">
        {search}
    </div>
</div>
HTML;

        $toolbar = [];

        // check settings component and module existence
        if (Yii::$app->has('settings') && Yii::$app->hasModule('settings')) {

            // check module permissions
            $settingPermission = false;
            if (Yii::$app->getModule('settings')->accessRoles === null) {
                $settingPermission = true;
            } else {
                foreach (Yii::$app->getModule('settings')->accessRoles as $role) {
                    $settingPermission = Yii::$app->user->can($role);
                }
            }

            if ($settingPermission) {
                $settings = [
                    'icon' => 'cogs',
                    'url' => ['/settings', 'SettingSearch' => ['section' => 'pages']],
                    'options' => [
                        'title' => Yii::t('pages', 'Settings'),
                        'class' => 'btn btn-info'
                    ]
                ];
                $toolbar[] = TreeView::BTN_SEPARATOR;
                $toolbar['settings'] = $settings;
            }
        }

        $mainTemplate = <<< HTML
<div class="row">
    <div class="col-md-5" id="dmstr-pages-detail-wrapper">
        <div class="box box-solid">
        {wrapper}
        </div>
    </div>
    <div class="col-md-7" id="dmstr-pages-detail-panel">
        {detail}
    </div>
</div>
HTML;


        PagesBackendAsset::register($this->view);
        $this->view->title = Yii::t('pages', 'Pages');

        return $this->render('index', [
            'queryTree' => $queryTree,
            'headerTemplate' => $headerTemplate,
            'toolbar' => $toolbar,
            'mainTemplate' => $mainTemplate
        ]);
    }

    /**
     * @return Yii\web\Response
     * @throws MethodNotAllowedHttpException
     * @throws Yii\base\InvalidConfigException
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
     * Redirect to URL for given pageId
     * This is useful if one will create multiple menu items to one existing content page
     *
     * @param $pageId
     *
     * @throws NotFoundHttpException
     */
    public function actionRefPage($pageId)
    {

        $page = Tree::findOne(['id' => $pageId]);
        if ($page && $page instanceof Tree) {
            return $this->redirect($page->createUrl());
        } else {
            throw new NotFoundHttpException();
        }
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
        Yii::$app->session->set('__crudReturnUrl', null);

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

        if ($this->module->pageCheckAccessDomain) {
            $pageQuery->andWhere(['access_domain' => [Yii::$app->language, Tree::$_all]]);
        }

        // get page
        /** @var $page Tree */
        $page = $pageQuery->one();

        // Show disabled pages for admins
        if ($page !== null && $page->isDisabled() && !Yii::$app->user->can('pages')) {
            $page = null;
        }

        // if the route of the $page does not point to $this->route, make a redirect to the destination url
        if ($page !== null && ltrim($page->route, '/') !== $this->route) {
            $destRoute = [$page->route];
            if (!empty($page->request_params) && Json::decode($page->request_params)) {
                $destRoute = ArrayHelper::merge($destRoute, Json::decode($page->request_params));
            }
            return $this->redirect($destRoute);
        }

        # reactivate access_* check in ActiveRecordAccessTrait::find for further queries
        Tree::$activeAccessTrait = true;
        // check if page has access_read permissions set, if yes check if user is allowed
        if (!empty($page->access_read) && $page->access_read !== '*') {
            if (!Yii::$app->user->can($page->access_read)) {
                # if userIsGuest, redirect to login page
                if (!Yii::$app->user->isGuest) {
                    throw new HttpException(403, Yii::t('pages', 'Forbidden'));
                }

                return $this->redirect(Yii::$app->user->loginUrl);
            }
        }

        if ($page !== null) {
            // Set page title, use name as fallback
            $this->view->title = $page->page_title ?: $page->name;

            // Register default SEO meta tags
            if (!empty($page->default_meta_keywords)) {
                $this->view->registerMetaTag(['name' => 'keywords', 'content' => $page->default_meta_keywords],'keywords');
            }

            if (!empty($page->default_meta_description)) {
                $this->view->registerMetaTag(['name' => 'description', 'content' => $page->default_meta_description], 'description');
            }

            // Render view
            if (empty($page->view)) {
                throw new HttpException(404, Yii::t('pages', 'Page not found.') . ' [ID: ' . $pageId . ']');
            }
            return $this->render($page->view, ['page' => $page]);
        } else {
            if ($fallbackPage = $this->resolveFallbackPage($pageId)) {
                Yii::trace('Resolved fallback URL for ' . $fallbackPage->id, __METHOD__);
                return $this->redirect($fallbackPage->createUrl(['language' => $fallbackPage->access_domain]));
            } else {
                throw new HttpException(404, Yii::t('pages', 'Page not found.') . ' [ID: ' . $pageId . ']');
            }
        }

        if ($fallbackPage = $this->resolveFallbackPage($pageId)) {
            Yii::trace('Resolved fallback URL for ' . $fallbackPage->id, __METHOD__);
            return $this->redirect($fallbackPage->createUrl(['language' => $fallbackPage->access_domain]));
        }

        throw new HttpException(404, Yii::t('pages', 'Page not found.') . ' [ID: ' . $pageId . ']');
    }


    /**
     * @param $pageId
     * @return Tree|bool
     */
    private function resolveFallbackPage($pageId)
    {

        if (!$this->module->pageUseFallbackPage) {
            return false;
        }

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
