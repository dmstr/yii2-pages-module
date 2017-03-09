<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2017 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\controllers;

use dmstr\modules\pages\models\forms\CopyForm;
use mikehaertl\shellcommand\Command;
use rmrevin\yii\fontawesome\AssetBundle;
use yii\helpers\Url;
use yii\web\Controller;


/**
 * Class CopyController
 * @package dmstr\modules\pages\controllers
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class CopyController extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'root-node';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Register font-awesome asset bundle
        AssetBundle::register(\Yii::$app->view);
    }
    
    /**
     * @return string
     */
    public function actionRootNode()
    {
        Url::remember();

        $model = new CopyForm();
        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {

            // RUN copy-pages cli command
            $yiiCommandPath = \Yii::getAlias('@vendor') . '/../yii';
            if ( ! file_exists($yiiCommandPath) || ! is_executable($yiiCommandPath)) {
                \Yii::$app->session->setFlash(
                    'danger',
                    \Yii::t(
                        'widgets',
                        'yii binary not found or is not executable in path {PATH}',
                        ['PATH' => $yiiCommandPath]
                    )
                );
                return $this->refresh();
            }
            $command = new Command($yiiCommandPath . ' copy-pages/root-node');
            $command->addArg('--rootId', $model->sourceRootId);
            $command->addArg('--destinationLanguage', $model->destinationLanguage);
            if ($command->execute() && empty($command->getError())) {
                \Yii::$app->session->setFlash('success', $command->getOutput());
            } else {
                \Yii::$app->session->setFlash('danger', $command->getError());
            }

            return $this->refresh();
        }
        return $this->render('root-node', ['copyForm' => $model]);
    }
}
