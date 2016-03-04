<?php
use tests\_pages\LoginPage;

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that Pages works');

$I->amGoingTo('try to check access control');
$I->amOnPage('/pages');
$I->see('Sign in', 'h3');
$I->makeScreenshot('pages-login');

$loginPage = LoginPage::openBy($I);
$I->see('Sign in', 'h3');
$I->amGoingTo('try to login with correct credentials');
$loginPage->login('admin', 'admin');
$I->dontSee('Sign in', 'h3');
$I->makeScreenshot('pages-after-login');

$I->amGoingTo('try to view and create pages');
$I->amOnPage('/pages');

$I->see('Pages', 'h1');
$I->makeScreenshot('success-pages-index');