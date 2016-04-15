Yii2 Page Manager
=================

Application sitemap and navigation manager module for Yii 2.0 Framework

**:warning: Breaking changes in 0.14.0 :warning:**

`data structure` and `public properties` are updated and query menu items from now on via `domain_id`

Requirements
------------

- URL manager from [codemix/yii2-localeurls](https://github.com/codemix/yii2-localeurls) configured in application
- role based access control; `auth_items` for every `module_controller_action`


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require dmstr/yii2-pages-module "^0.14.0"
```

or add

```
"dmstr/yii2-pages-module": "^0.14.0"
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

by `domain_id` i.e. `root` 

```
$menuItems = \dmstr\modules\pages\models\Tree::getMenuItems('root');
```

*use for example with bootstrap Navbar*

```
    echo yii\bootstrap\Nav::widget(
        [
            'options'         => ['class' => 'navbar-nav navbar-right'],
            'activateItems'   => false,
            'encodeLabels'    => false,
            'activateParents' => true,
            'items'           => Tree::getMenuItems('root'),
        ]
    );
```

#### Backend

- visit `/pages` to create a root-node for your current application language.
- click the *tree* icon
- enter `name identifier (no spaces and special chars)` as *Domain ID* and *Menu name* and save
- create child node
- assign name, title, language and route/view
- save

Now you should be able to see the page in your `Nav` widget in the frontend of your application.

#### Anchors

*available since 0.12.0-beta1*

:construction_worker: A workaround for creating anchor links is to define a route, like `/en/mysite-2` in the settings module.
On a node you can attach an anchor by using *Advanced URL settings*, with `{'#':'myanchor'}`.

It is recommended to create a new entry in *Tree* mode.

Testing
-------

Requirements:

 - docker >=1.9.1
 - docker-compose >= 1.6.2

Codeception is run via "Potemkin"-Phundament.


    cd tests

Start test stack    
    
    docker-compose up -d

 Run a bash in the container
 
    docker-compose run --rm phpfpm bash

Setup
    
    $ setup.sh
    
Run the tests
    
    $ YII_ENV=test codecept run unit,acceptance

Ressources
----------

tbd

---

Built by [dmstr](http://diemeisterei.de)