<?php

// @group optional

use dmstr\modules\pages\tests\codeception\_pages\LoginPage;

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that Pages works');

$I->expect('no access with guest user');
$I->amOnPage('/de/pages');
$I->seeElement('//form');
$I->seeElement('input', ['name' => 'login-form[login]']);
$I->makeScreenshot('pages-login');

$I->amGoingTo('try to login with correct credentials');
$loginPage = new LoginPage();
$loginPage->login($I, 'admin', 'admin1');
$I->makeScreenshot('pages-after-login');

$I->amGoingTo('try to view pages');
$I->amOnPage('/de/pages');
$I->see('Pages', 'h1');
$I->makeScreenshot('success-pages-index');