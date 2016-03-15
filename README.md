Yii2 Page Manager
=================

Application sitemap and navigation manager module for Yii 2.0 Framework


Requirements
------------

- URL manager from [codemix/yii2-localeurls](https://github.com/codemix/yii2-localeurls) configured in application
- role based access control; `auth_items` for every `module_controller_action`


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require dmstr/yii2-pages-module "*"
```

or add

```
"dmstr/yii2-pages-module": "*"
```

to the require section of your `composer.json` file.


Setup
-----

Run migrations

```
./yii migrate \
    --disableLookup=1 \
    --migrationPath=@vendor/dmstr/yii2-pages-module/migrations
```


Configuration
-------------

Enable module in application configuration

    'pages' => [
        'class' => 'dmstr\modules\pages\Module',
        'layout' => '@admin-views/layouts/main',
        'availableRoutes' => [
            '/site/index' => '/site/index',
        ],
    ],


Use settings module to configure additional controllers

- Add one controller route per line to section `pages`, key `availableRoutes`


Usage
-----

#### Navbar (eg. `layouts/main`) 

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

#### Backend

- visit `/pages` to create a root-node for your current application language.
- click the *tree* icon
- enter `root_LANG` as *Name ID* and *Name* and save
- create child node
- assign name, title, language and route/view
- save

Now you should be able to see the page in your `Nav` widget in the frontend of your application.


Testing
-------

Requirements:

 - Docker >=1.9.1

Codeception is run via "Potemkin"-Phundament.


    cd tests

Start test stack    
    
    docker-compose up -d

 Run a bash in the container
 
    docker-compose run --rm phpfpm bash

Setup
    
    $ setup.sh
    
Run the tests
    
    $ YII_ENV=test codecept run -c /app/vendor/dmstr/yii2-pages-module/codeception.yml

Ressources
----------

tbd

---

Built by [dmstr](http://diemeisterei.de)