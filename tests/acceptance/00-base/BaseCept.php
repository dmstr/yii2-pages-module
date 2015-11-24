<?php
use tests\_pages\LoginPage;

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that Pages works');

$loginPage = LoginPage::openBy($I);
$I->see('Sign in', 'h3');
$I->amGoingTo('try to login with correct credentials');
$loginPage->login('admin', 'admin');
$I->amGoingTo('try to view and create pages');
$I->amOnPage('/pages');

$I->see('Pages', 'h2');
#$I->see('Widgets-Module', 'h1');
$I->makeScreenshot('success-pages-index');