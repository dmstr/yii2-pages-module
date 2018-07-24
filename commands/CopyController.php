<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2017 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\commands;

use dmstr\modules\pages\models\BaseTree;
use dmstr\modules\pages\models\Tree;
use hrzg\widget\models\crud\WidgetContent;
use hrzg\widget\models\crud\WidgetContentTranslation;
use hrzg\widget\models\crud\WidgetContentTranslationMeta;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\helpers\Console;
use yii\helpers\Url;

/**
 * Pages module copy command
 * @package dmstr\modules\pages\commands
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class CopyController extends \yii\console\Controller
{
    /**
     * @const string
     */
    const DESCRIPTION = "Pages module copy command";

    /**
     * @var integer
     */
    public $rootId;

    /**
     * @var string
     */
    public $destinationLanguage;

    /**
     * @param string $id
     *
     * @return array
     */
    public function options($id)
    {
        return array_merge(
            parent::options($id),
            [
                'rootId',
                'destinationLanguage',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Show information about this command
     */
    public function actionIndex()
    {
        $actions = [
            $this->id . '/root-node',
        ];
        echo "\n" . self::DESCRIPTION . "\n";
        echo "----------------------------------------\n\n";
        foreach ($actions as $action) {
            echo "yii " . $action . "\n";
        }
        echo "\n\n";
    }

    /**
     * Copy a root node to another language
     *
     * @param $rootId
     * @param $destinationLanguage
     *
     * @return bool
     */
    public function actionRootNode($rootId, $destinationLanguage)
    {
        // disable access trait
        Tree::$activeAccessTrait = false;

        // transaction begin
        $transaction = \Yii::$app->db->beginTransaction();

        // try copy root node with children
        try {
            /**
             * Find source root node
             *
             * @var Tree $sourceRootNode
             */
            $sourceRootNode = Tree::findOne([Tree::ATTR_ID => $rootId, Tree::ATTR_LVL => Tree::ROOT_NODE_LVL]);
            if ($sourceRootNode === null) {
                throw new Exception(\Yii::t('pages', 'Root node with ID={ID} not found', ['ID' => $rootId]), 404);
            }

            /**
             * make new root in destination language
             */

            // check if not already exists
            $newRootNodeExists = Tree::findOne(
                [Tree::ATTR_DOMAIN_ID => $sourceRootNode->domain_id, Tree::ATTR_ACCESS_DOMAIN => $destinationLanguage]
            );


            if ($newRootNodeExists instanceof Tree) {
                throw new Exception(
                    \Yii::t(
                        'pages',
                        'Root node with domain_id="{DOMAIN_ID}" and access_domain="{ACCESS_DOMAIN}" already exists',
                        ['DOMAIN_ID' => $sourceRootNode->domain_id, 'ACCESS_DOMAIN' => $destinationLanguage]
                    ), 500
                );
            }

            // make new root node
            $newRootNode = new Tree($sourceRootNode->attributes);
            $newRootNode->id = null;
            $newRootNode->name = str_replace(
                $sourceRootNode->access_domain,
                $destinationLanguage,
                $sourceRootNode->name
            );
            $newRootNode->access_domain = $destinationLanguage;
            $newRootNode->created_at = new Expression('NOW()');
            $newRootNode->updated_at = new Expression('NOW()');

            // detach nested set behavior to be able to raw insert records
            $newRootNode->detachBehavior('tree');
            $newRootNode->save();

            // set the new page id as root
            $newRootNode->root = $newRootNode->id;
            $newRootNode->save();


            if (!empty($newRootNode->getErrors())) {
                throw new Exception(implode(', ', $newRootNode->getErrors()));
            }


            // make new child leaves in destination language
            $childLeaveQuery = Tree::find()->where([Tree::ATTR_ROOT => $sourceRootNode->id]);
            $childLeaveQuery->andWhere(['NOT', [Tree::ATTR_ID => $sourceRootNode->id]]);


            foreach ($childLeaveQuery->all() as $sourceChildLeave) {
                /**
                 * make new child leave
                 *
                 * @var Tree $childLeave
                 */
                $newChildNode = new Tree($sourceChildLeave->attributes);
                $newChildNode->id = null;
                $newChildNode->root = $newRootNode->id;
                $newChildNode->access_domain = $destinationLanguage;
                $newChildNode->created_at = new Expression('NOW()');
                $newChildNode->updated_at = new Expression('NOW()');

                // detach nested set behavior to be able to raw insert records
                $newChildNode->detachBehavior('tree');
                $newChildNode->save();

                if (!empty($newChildNode->getErrors())) {
                    throw new Exception(implode(', ', $newRootNode->getErrors()));
                }
            }

            // Success
            $this->stdout('"' . Tree::optsSourceRootId()[$rootId] . '" successfully copied to language "' . $destinationLanguage . '"');
            $transaction->commit();
            \Yii::$app->end();
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->stderr($e->getMessage());
            \Yii::$app->end(1);
        }
    }

    /**
     * Copy one node with widgets to any parent
     *
     * @param int $sourceId Id from page which will be used as copy template
     * @param int $targetParentId If from target parent
     * @return int Exit code: 0 On success and 74 on error
     */
    public function actionNode($sourceId, $targetParentId)
    {

        $sourcePage = Tree::findOne($sourceId);

        // check if source page exists
        if ($sourcePage === null) {
            $this->stderr("Page does not exist.\n", Console::FG_RED);
            return ExitCode::IOERR;
        }

        $targetParentPage = Tree::find()->where(['id' => $targetParentId, 'route' => BaseTree::DEFAULT_PAGE_ROUTE])->one();

        // check if target parent page exist
        if ($targetParentPage === null) {
            $this->stderr("Root node does not exist or is no default page.\n", Console::FG_RED);
            return ExitCode::IOERR;
        }

        // defining new name, default is a random string with prefix
        $name = $this->prompt("\nNew page name:", ['default' => uniqid('new-page-', false)]);

        $newPage = new Tree([
            'name' => $name,
            'access_domain' => $targetParentPage->access_domain,
            'route' => $targetParentPage->route,
            'view' => $targetParentPage->view
        ]);

        $newPage->appendTo($targetParentPage);

        // if new page cannot be saved then print errors
        if (!$newPage->save()) {
            $this->stderr("Cannot save new page:\n\n", Console::FG_RED);
            foreach ($newPage->errors as $errorSummary) {
                foreach ($errorSummary as $error) {
                    $this->stderr("{$error}\n", Console::FG_RED);
                }
            }
            return ExitCode::IOERR;
        }

        // show text on success
        $this->stdout("Created page '{$name}' on parent {$targetParentPage->name}\n\n", Console::FG_GREEN, Console::BOLD);
        $this->stdout("Copy widgets from parent...\n\n", Console::FG_YELLOW, Console::BOLD);

        $widgetContent = new WidgetContent(['timezone' => \Yii::$app->getModule('widgets')->timezone]);

        // trim slash from default page route because widgets saves routes like this...
        $widgets = $widgetContent::find()->where(['route' => ltrim(BaseTree::DEFAULT_PAGE_ROUTE, '/'), 'request_param' => $sourceId])->with('translations')->all();

        foreach ($widgets as $widget) {
            $error = false;
            $newWidget = new WidgetContent();

            $newWidget->attributes = $widget->attributes;

            $newWidget->id = null;
            $newWidget->status = '1';
            $newWidget->request_param = (string)$newPage->id;
            $newWidget->domain_id = uniqid('', false);

            if (!$newWidget->save()) {
                $this->stderr("Error while adding widget.\n", Console::FG_RED);
                $error = true;
            } else {
                $this->stdout("Added widget with id {$newWidget->id} to new page.\n", Console::FG_GREEN, Console::BOLD);
            }

            // add translations
            $translationCount = 0;
            /** @var WidgetContentTranslation $translation */
            foreach ($widget->translations as $translation) {
                $newTranslation = new WidgetContentTranslation();
                $newTranslation->attributes = $translation->attributes;
                $newTranslation->widget_content_id = $newWidget->id;
                if (!$newTranslation->save()) {
                    $this->stderr("  - Error while adding widget translation.\n", Console::FG_RED);
                    $error = true;
                } else {
                    $this->stdout("  - Added widget translation for id {$newWidget->id}.\n");
                    $translationCount++;
                }
            }

            // add translation meta
            $metaCount = 0;
            /** @var WidgetContentTranslationMeta $meta */
            foreach ($widget->translationsMeta as $meta) {
                $newMeta = new WidgetContentTranslationMeta();
                $newMeta->attributes = $meta->attributes;
                $newMeta->widget_content_id = $newWidget->id;
                if (!$newMeta->save()) {
                    $this->stderr("  - Error while adding widget translation meta.\n", Console::FG_RED);
                    $error = true;
                } else {
                    $this->stdout("  - Added widget translation meta for id {$newWidget->id}.\n");
                    $metaCount++;
                }
            }

            // error handling
            if ($error && $this->confirm('An error occurred. Revert?')) {
                WidgetContentTranslationMeta::deleteAll(['widget_content_id' => $newWidget->id]);
                WidgetContentTranslation::deleteAll(['widget_content_id' => $newWidget->id]);
                WidgetContent::deleteAll(['id' => $newWidget->id]);
                $this->stderr("Reverted adding widget.\n", Console::FG_RED, Console::BOLD);
            }
        }

        $this->stdout("\nSuccess! You can access the new page at: ", Console::FG_YELLOW, Console::BOLD);
        $this->stdout(Url::to($newPage->createRoute()) . "\n", Console::FG_BLUE);

        return ExitCode::OK;
    }
}
