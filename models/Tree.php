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
        // Get root node by name
        $rootNode = self::findOne(['name' => $rootName]);

        if ($rootNode === null) {
            return [];
        }

        // Get all leaves from this root node
        $leaves = $rootNode->children()->all();

        if ($leaves === null) {
            return [];
        }

        // tree mapping and leave stack
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
                    'linkOptions' => $nodeOptions,
                ];
                $item          = $itemTemplate;

                // Count items in stack
                $counter = count($stack);

                // Check on different levels
                while ($counter > 0 && $stack[$counter - 1]['linkOptions']['data-lvl'] >= $item['linkOptions']['data-lvl']) {
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
                    if (!isset($stack[$counter - 1]['items'])) {
                        $stack[$counter - 1]['items'] = [];
                    }
                    // add the node to parent node
                    $i                                = count($stack[$counter - 1]['items']);
                    $stack[$counter - 1]['items'][$i] = $item;
                    $stack[]                          = &$stack[$counter - 1]['items'][$i];
                }
            }
        }
        return array_filter($treeMap);
    }




    /**
     * @param array $additionalParams
     * @param bool $absolute
     *
     * @return mixed
     */
    public function createUrl($additionalParams = array(), $absolute = false)
    {

        if (is_array(CJSON::decode($this->route)) && count(CJSON::decode($this->route)) !== 0) {
            $link = CJSON::decode($this->route);
        } else {
            $link['route']  = '/p3pages/default/page';
            $link['params'] = CMap::mergeArray(
                $additionalParams,
                array(
                    P3Page::PAGE_ID_KEY   => $this->id,
                    P3Page::PAGE_NAME_KEY => $this->t('seoUrl', null, true)
                )
            );
        }

        if (isset($link['route'])) {
            $params = (isset($link['params'])) ? $link['params'] : array();
            if ($absolute === true) {
                return Yii::app()->controller->createAbsoluteUrl($link['route'], $params);
            } else {
                return Yii::app()->controller->createUrl($link['route'], $params);
            }
        } elseif (isset($link['url'])) {
            return $link['url'];
        } else {
            Yii::log('Could not determine URL string for P3Page #' . $this->id, CLogger::LEVEL_WARNING);
        }
    }
}