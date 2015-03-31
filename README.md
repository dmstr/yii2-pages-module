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

#### site/index action in controllers/SiteController

```
        /**
         * Site/index for use with dmstr/yii2-pages-module
         * @return string
         * @throws HttpException
         */
        use dmstr\modules\pages\models\Tree;
     
        public function actionIndex()
        {
            Url::remember();
            $this->layout = '@app/views/layouts/main';
    
            $localizedRoot = 'root_' . \Yii::$app->language;
            $page          = Tree::findOne(
                [
                    Tree::ATTR_NAME_ID => $localizedRoot,
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
                \Yii::info(\Yii::t('app', 'Pages: Root node anlegen.'), 'pages');
                \Yii::warning(\Yii::t('app', 'Page not found.') . ' [NameID: ' . $localizedRoot . ']', 'pages');
                $this->redirect(['/pages']);
            }
        }
```



#### layouts/main render Navbar

*find a root node / leave node*

by `name_id`

```
$localizedRoot = 'root_' . \Yii::$app->language;
$menuItems = \dmstr\modules\pages\models\Tree::getMenuItems($localizedRoot);
```

*use for example with bootstrap Navbar*

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


