<?php

// @group mandatory

use dmstr\modules\pages\tests\codeception\_pages\LoginPage;

$I = new E2eTester($scenario);
$I->wantTo('ensure that Pages works');

$I->amGoingTo('login');
$I->amOnPage('/de/pages');
$I->seeElement('//form');
$I->seeElement('input', ['name' => 'login-form[login]']);
$I->makeScreenshot('view-login-page');

$I->amGoingTo('try to login with correct credentials');
$loginPage = new LoginPage();
$loginPage->login($I, 'admin', 'admin1');
$I->makeScreenshot('pages-after-login');

$I->amGoingTo('try to view pages');
$I->amOnPage('/de/pages');
// Copy pages link
$I->seeElement('.kv-icon-10.fa.fa-copy');
// Settings link
$I->seeElement('.kv-icon-10.fa.fa-cogs');
$I->makeScreenshot('success-pages-index');