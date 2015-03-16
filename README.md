Yii2 Page Manager
=================
Application sitemap and navigation manager module for Yii 2.0 Framework

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist dmstr/yii2-pages-module "*"
```

or add

```
"dmstr/yii2-pages-module": "*"
```

to the require section of your `composer.json` file.


Database
--------
```
fig run web ./yii migrate \
    --disableLookup=1 \
    --migrationPath=@vendor/dmstr/yii2-pages-module/migrations
```

Usage
-----

**find a root node / leave node**

by `name_id`

```
$localizedRoot = 'root_' . \Yii::$app->language;
$menuItems = \dmstr\modules\pages\models\Tree::getMenuItems($localizedRoot);
```

**use for example with bootstrap Navbar**

```
$localizedRoot = 'root_' . \Yii::$app->language;
    echo yii\bootstrap\Nav::widget(
        [
            'options'         => ['class' => 'navbar-nav navbar-right'],
            'activateItems'   => false,
            'encodeLabels'    => false,
            'activateParents' => true,
            'items'           => Tree::getMenuItems($localizedRoot),
        ]
    );
```
