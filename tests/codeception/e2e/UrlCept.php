<?php

// @group mandatory

use dmstr\modules\pages\tests\codeception\_pages\LoginPage;

$I = new E2eTester($scenario);
$I->wantTo('ensure that Page URL rules work');

$I->amGoingTo('try to view a page with different url rule patterns');
$I->amOnPage('/p/test-urls-2.html');
$I->dontSee('Page not found.');
$I->makeScreenshot('success-pages-url-1');

$I->amOnPage('/de/page/test-urls-2.html');
$I->dontSee('Page not found.');
$I->makeScreenshot('success-pages-url-2');

$I->amOnPage('/de/test-urls-2');
$I->dontSee('Page not found.');
$I->makeScreenshot('success-pages-url-3');