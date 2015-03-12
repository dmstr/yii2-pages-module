<?php

namespace dmstr\modules\pages\models;

use Yii;

class Tree extends \kartik\tree\models\Tree
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dmstr_pages';
    }

    /**
     * Override isDisabled method if you need as shown in the
     * example below. You can override similarly other methods
     * like isActive, isMovable etc.
     */
    public function isDisabled()
    {
        //if (Yii::$app->user->id !== 'admin') {
        //return true;
        //}

        return parent::isDisabled();
    }

    /**
     * @param $rootName the name of the root node
     *
     * @return array
     */
    public static function getMenuItems($rootName)
    {
        // Get page tree by root node name
        $rootNode   = self::findOne(['name' => $rootName]);
        $leaves     = $rootNode->children()->all();

        // tree mapping
        $treeMap = [];
        $stack   = [];

        if (count($leaves) > 0) {

            foreach ($leaves as $node) {

                // prepare node identifiers
                $nodeOptions = [
                    'data-pageId' => $node->id,
                    'data-lvl'    => $node->lvl,
                ];

                $itemTemplate  = [
                    'label'       => $node->name,
                    'url'         => '',// TODO $node->createUrl(),
                    'active'      => $node->active,
                    'itemOptions' => $nodeOptions,
                ];
                $item          = $itemTemplate;
                $item['items'] = [];

                // Count items in stack
                $counter = count($stack);

                // Check on different levels
                while ($counter > 0 && $stack[$counter - 1]['itemOptions']['data-lvl'] >= $item['itemOptions']['data-lvl']) {
                    array_pop($stack);
                    $counter--;
                }

                // Stack is now empty (check root again)
                if ($counter == 0) {
                    // assign root node
                    $i           = count($treeMap);
                    $treeMap[$i] = $item;
                    $stack[]     = &$treeMap[$i];
                } else {
                    // add the node to parent node
                    $i                                = count($stack[$counter - 1]['items']);
                    $stack[$counter - 1]['items'][$i] = $item;
                    $stack[]                          = &$stack[$counter - 1]['items'][$i];
                }
            }
        }
        return array_filter($treeMap);
    }
}