<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\controllers;

use dmstr\modules\pages\models\Tree;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\View;

/**
 * Class DefaultController
 * @package dmstr\modules\pages\controllers
 * @author $Author
 */
class DefaultController extends Controller
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
                        'actions' => ['page'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'matchCallback' => function ($rule, $action) {
                            return
                                \Yii::$app->user->can(
                                    strtr(
                                        $this->module->id,
                                        ['/' => '_', '-' => '_']
                                    )
                                ) ||
                                \Yii::$app->user->can(
                                    strtr(
                                        $this->module->id . '/' . $this->id,
                                        ['/' => '_', '-' => '_']
                                    )
                                ) ||
                                \Yii::$app->user->can(
                                    strtr(
                                        $this->module->id . '/' . $this->id . '/' . $action->id,
                                        ['/' => '_', '-' => '_']
                                    )
                                ) ||
                                (\Yii::$app->user->identity && \Yii::$app->user->identity->isAdmin);
                        },
                    ]
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        if (!$this->module->getLocalizedRootNode()) {
            $language = \Yii::$app->language;

            $msg = <<<HTML
<b>Localized root-node missing</b>
<p>
Please create a new root-node for the current language.
</p>
<p>
<a onclick="$('#tree-name_id').val('root_{$language}');$('#tree-name').val('root_{$language}');$('.kv-detail-container button[type=submit]').click()" class="btn btn-warning btn-lg">Create root-node for <b>{$language}</b></a>
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
     * renders a page view from the database
     *
     * @param $id
     * @param null $pageName
     * @param null $parentLeave
     *
     * @return string
     * @throws HttpException
     */
    public function actionPage($id, $pageName = null, $parentLeave = null)
    {
        Url::remember();
        \Yii::$app->session['__crudReturnUrl'] = null;

        // Set layout
        $this->layout = '@app/views/layouts/main';

        // Get active Tree object, allow access to invisible pages
        // @todo: improve handling, using also roles
        $pageQuery = Tree::find()->where(
            [
                Tree::ATTR_ID => $id,
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
            // Set page title
            $this->view->title = $page->page_title;

            // Register default SEO meta tags
            $this->view->registerMetaTag(['name' => 'keywords', 'content' => $page->default_meta_keywords]);
            $this->view->registerMetaTag(['name' => 'description', 'content' => $page->default_meta_description]);

            // Render view
            return $this->render($page->view, ['page' => $page]);
        } else {
            throw new HttpException(404, \Yii::t('app', 'Page not found.') . ' [ID: ' . $id . ']');
        }
    }
}
