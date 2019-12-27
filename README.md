Yii2 Page Manager
=================

[![Latest Stable Version](https://poser.pugx.org/dmstr/yii2-pages-module/v/stable.svg)](https://packagist.org/packages/dmstr/yii2-pages-module) 
[![Total Downloads](https://poser.pugx.org/dmstr/yii2-pages-module/downloads.svg)](https://packagist.org/packages/dmstr/yii2-pages-module)
[![License](https://poser.pugx.org/dmstr/yii2-pages-module/license.svg)](https://packagist.org/packages/dmstr/yii2-pages-module)

Application sitemap and navigation manager module for Yii 2.0 Framework

**:warning: Breaking changes in 0.14.0 and 0.18.0**

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

```
// module configuration
'pages' => [
	'class' => 'dmstr\modules\pages\Module',
	'layout' => '@admin-views/layouts/main',
	'roles' => ['Admin', 'Editor'],
	'availableRoutes' => [
		'site/index' => 'Index Route',
	]
],

```

Use settings module to configure additional controllers

- Add one controller route per line to section `pages`, key `availableRoutes`

### Settings

- `pages.availableRoutes` - routes per access_domain (for non-admin users)
- `pages.availableGlobalRoutes` - global routes (for admin users)


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

#### Traits

We use the `\dmstr\db\traits\ActiveRecordAccessTrait` to have a check access behavior on active record level

- Owner Access
- Read Access
- Update Access
- Delete Access


#### Anchors

*available since 0.12.0-beta1*

:construction_worker: A workaround for creating anchor links is to define a route, like `/en/mysite-2` in the settings module.
On a node you can attach an anchor by using *Advanced URL settings*, with `{'#':'myanchor'}`.

It is recommended to create a new entry in *Tree* mode.


#### i18n - sibling pages

Find sibling page in target language

```
/**
 * Find the sibling page in target language if exists
 *
 * @param string $targetLanguage
 * @param integer $sourceId
 * @param string $route
 *
 * @return Tree|null
 * @throws \yii\console\Exception
 */
public function sibling($targetLanguage, $sourceId = null, $route = self::DEFAULT_PAGE_ROUTE);


Example 1:
---

// page id 12 is a node in language 'en'
$sourcePage = Tree::findOne(12);

// returns corresponding page object in language 'de' or null if not exists
$targetPage = $sourcePage->sibling('de');


Example 2:
---

// find by params
$targetPage = (new Tree())->sibling('de', 12, '/pages/default/page')

```


Testing
-------

Requirements:

 - docker >=1.9.1
 - docker-compose >= 1.6.2

Codeception is run via "Potemkin"-Phundament.


    cd tests

Start test stack    
    
    make all

Run tests

    make run-tests
    

Ressources
----------

tbd

---

### ![dmstr logo](http://t.phundament.com/dmstr-16-cropped.png) Built by [dmstr](http://diemeisterei.de)
