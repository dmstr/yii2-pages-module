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
use yii\web\HttpException;

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
                        'actions' => ['index'],
                        'roles'   => ['@']
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['page'],
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
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        if (!$this->module->getLocalizedRootNode()) {
            $language = \Yii::$app->language;
            $msg      = "<b>Localized root-node missing</b><br/>Please create a new root node for the current language, with <b>Name</b> and <b>Name ID</b> <code>root_{$language}</code>";
            \Yii::$app->session->addFlash('warning', $msg);
        }

        return $this->render('index');
    }

    public function actionPage($id, $pageName = null, $parentLeave = null)
    {
        Url::remember();
        \Yii::$app->session['__crudReturnUrl'] = null;

        // Set layout
        $this->layout = '@app/views/layouts/main';

        // Get Tree object
        $page = Tree::findOne(
            [
                Tree::ATTR_ID      => $id,
                Tree::ATTR_ACTIVE  => Tree::ACTIVE,
                Tree::ATTR_VISIBLE => Tree::VISIBLE
            ]
        );

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
