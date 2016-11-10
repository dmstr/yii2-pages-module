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

use dmstr\modules\pages\models\Tree;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\View;

/**
 * Class DefaultController.
 *
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class DefaultController extends Controller
{
    /**
     * @return mixed
     */
    public function actionIndex()
    {
        if (!$this->module->getLocalizedRootNode()) {
            $language = mb_strtolower(\Yii::$app->language);
            $rootNodePrefix = Tree::ROOT_NODE_PREFIX;

            $msg = <<<HTML
<b>Localized root-node missing</b>
<p>
Please create a new root-node for the current language.
</p>
<p>
<a onclick="$('#tree-domain_id').val('{$rootNodePrefix}');$('#tree-name').val('{$rootNodePrefix}_{$language}');$('.kv-detail-container button[type=submit]').click()" class="btn btn-warning btn-lg">Create root-node for <b>{$language}</b></a>
</p>
HTML;

            $js = <<<'JS'
$(".kv-create-root").click();
JS;

            $this->getView()->registerJs($js, View::POS_LOAD);
            \Yii::$app->session->addFlash('warning', $msg);
        }

        return $this->render('index');
    }

    /**
     * renders a page view from the database.
     *
     * @param $pageId
     * @param null $pageName
     * @param null $parentLeave
     *
     * @return string
     *
     * @throws HttpException
     */
    public function actionPage($pageId, $pageName = null, $parentLeave = null)
    {
        Url::remember();
        \Yii::$app->session['__crudReturnUrl'] = null;

        // Set layout
        $this->layout = '@app/views/layouts/main';

        // Get active Tree object, allow access to invisible pages
        // @todo: improve handling, using also roles
        $pageQuery = Tree::find()->where(
            [
                Tree::ATTR_ID => $pageId,
                Tree::ATTR_ACTIVE => Tree::ACTIVE,
                Tree::ATTR_ACCESS_DOMAIN => \Yii::$app->language,
            ]
        );

        // Show disabled pages for admins
        if (!\Yii::$app->user->can('pages')) {
            $pageQuery->andWhere(
                [
                    Tree::ATTR_DISABLED => Tree::NOT_DISABLED,
                ]
            );
        }

        // get page
        $page = $pageQuery->one();

        if ($page !== null) {
            // Set page title, use name as fallback
            $this->view->title = $page->page_title ?: $page->name;

            // Register default SEO meta tags
            $this->view->registerMetaTag(['name' => 'keywords', 'content' => $page->default_meta_keywords]);
            $this->view->registerMetaTag(['name' => 'description', 'content' => $page->default_meta_description]);

            // Render view
            return $this->render($page->view, ['page' => $page]);
        } else {
            if ($fallbackPage = $this->resolveFallbackPage($pageId)) {
                \Yii::trace('Resolved fallback URL for '.$fallbackPage->id, __METHOD__);
                return $this->redirect($fallbackPage->createUrl(['language' => $fallbackPage->access_domain]));
            } else {
                throw new HttpException(404, \Yii::t('pages', 'Page not found.').' [ID: '.$pageId.']');
            }
        }
    }


    /**
     * @return array
     */
    private function resolveFallbackPage($pageId)
    {
        $original = Tree::find()->where(['id' => $pageId])->one();
        $fallback = Tree::find()->where(['domain_id' => $original->domain_id, 'access_domain' => \Yii::$app->language])
            ->one();
        return $fallback;
    }
}
