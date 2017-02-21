<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2017 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace dmstr\modules\pages\commands;

use dmstr\modules\pages\models\Tree;
use yii\console\Exception;
use yii\db\Expression;

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
            $newRootNode                = new Tree($sourceRootNode->attributes);
            $newRootNode->id            = null;
            $newRootNode->name          = str_replace(
                $sourceRootNode->access_domain,
                $destinationLanguage,
                $sourceRootNode->name
            );
            $newRootNode->access_domain = $destinationLanguage;
            $newRootNode->created_at    = new Expression('NOW()');
            $newRootNode->updated_at    = new Expression('NOW()');

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
                $newChildNode                = new Tree($sourceChildLeave->attributes);
                $newChildNode->id            = null;
                $newChildNode->root          = $newRootNode->id;
                $newChildNode->access_domain = $destinationLanguage;
                $newChildNode->created_at    = new Expression('NOW()');
                $newChildNode->updated_at    = new Expression('NOW()');

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
}
