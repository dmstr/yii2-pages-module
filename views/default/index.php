<?php
/* @var $this yii\web\View */

use kartik\tree\TreeView;
use dmstr\modules\pages\models\Tree;

echo "<h1>Pages</h1>";

echo TreeView::widget(
    [
        // single query fetch to render the tree
        'query'          => Tree::find()->addOrderBy('root, lft'),
        'headingOptions' => ['label' => 'Categories'],
        'fontAwesome'    => true,     // optional
        'isAdmin'        => true,         // optional (toggle to enable admin mode)
        'displayValue'   => 1,        // initial display value
        'softDelete'     => true,    // normally not needed to change
        //'cacheSettings' => ['enableCache' => true] // normally not needed to change
    ]
);

/**
 * Playground for generating structured menuItems array
 */
function getMenuItems($rootNode)
{
    // tree mapping
    $treeMap = [];
    $stack = [];

    if (count($rootNode) > 0) {

        foreach ($rootNode as $node) {

            // prepare node identifiers
            $nodeOptions  = [
                'data-pageId' => $node->id,
                'data-lvl' => $node->lvl,
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

$rootNode = Tree::find()->addOrderBy('root, lft')->all();
\yii\helpers\VarDumper::dump(getMenuItems($rootNode), 25, true);

