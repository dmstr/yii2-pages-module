<?php
use dmstr\modules\pages\tests\_pages\LoginPage;

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that Pages works');

$loginPage = LoginPage::openBy($I);

$I->see('Sign in', 'h3');

$I->amGoingTo('try to login with correct credentials');
$loginPage->login('admin', 'admin');
$I->amGoingTo('try to view and create pages');
$I->amOnPage('/pages');
$I->wait(1);

$I->see('Pages-Module', '.kv-heading-container');
$I->makeScreenshot('success-pages-index');

$I->click('.kv-create-root');
$I->wait(3); // only for selenium

$I->see('General');
$I->see('Route');

$I->fillField('#tree-domain_id','root');
$I->fillField('#tree-name','Home');

$I->click('Save');
$I->wait(2);
$I->makeScreenshot('success-pages-create-root');

$I->click('#w0-detail');
$I->wait(1);
$I->click('.kv-node-label');
$I->wait(1);
$I->click('.kv-create');
$I->wait(2);

$I->fillField('#tree-name',uniqid('node-'));
$I->click('Save');
$I->wait(2);
$I->see('The node was successfully created');
$I->makeScreenshot('success-pages-create-node');