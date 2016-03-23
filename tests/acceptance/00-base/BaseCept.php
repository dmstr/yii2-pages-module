<?php
use dmstr\modules\pages\tests\_pages\LoginPage;

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that Pages works');

$I->expect('no access with guest user');
$I->amOnPage('/pages');
$I->see('Sign in', 'h3');
$I->makeScreenshot('pages-login');

$I->amGoingTo('try to login with correct credentials');
$loginPage = LoginPage::openBy($I);
$I->see('Sign in', 'h3');
$loginPage->login('admin', 'admin');
$I->dontSee('Sign in', 'h3');
$I->makeScreenshot('pages-after-login');

$I->amGoingTo('try to view and create pages');
$I->amOnPage('/pages');
$I->see('Pages', 'h1');
$I->makeScreenshot('success-pages-index');