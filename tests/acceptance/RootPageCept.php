<?php
#use dmstr\modules\pages\tests\_pages\LoginPage;

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that Pages works');

#$loginPage = LoginPage::openBy($I);
#$I->see('Sign in', 'h3');
#$I->amGoingTo('try to login with correct credentials');
#$loginPage->login('admin', 'admin');

$I->amGoingTo('try to view and create pages');
$I->amOnPage('/pages');
$I->wait(1);

$I->see('Nodes', '.kv-heading-container');
$I->makeScreenshot('success-pages-index');

$I->click('.kv-create-root');
#$I->waitForElementNotVisible('.form-vertical');
$I->waitForElementVisible('.form-vertical');
#$I->wait(3); // only for selenium

$I->see('General');
$I->see('Route');

$I->fillField('#tree-domain_id', uniqid('test-'));
$I->fillField('#tree-name','Test');

$I->click('Save');
$I->waitForElementVisible('.kv-tree');
$I->click('.kv-node-detail');

$I->waitForElementVisible('#pages-detail-panel h3');
$I->see('Test', '#pages-detail-panel h3');
$I->makeScreenshot('success-pages-create-root');

$I->click('#w0-detail');
$I->click('.kv-create');
$I->waitForElementVisible('.form-vertical');

$nodeId = uniqid('node-');
$I->fillField('#tree-domain_id', $nodeId);
$I->fillField('#tree-name', $nodeId);
$I->click('Save');
$I->wait(1);
$I->see($nodeId, '.kv-tree');
#$I->see('The node was successfully created');
$I->makeScreenshot('success-pages-create-node');