<?php

// @group mandatory

use dmstr\modules\pages\tests\codeception\_pages\LoginPage;

$I = new E2eTester($scenario);
$I->wantTo('ensure that Pages works');

$I->amGoingTo('try to view pages');
$I->amOnPage('/pages');
// Copy pages link
$I->seeElement('.kv-icon-10.fa.fa-copy');
// Settings link
$I->seeElement('.kv-icon-10.fa.fa-cogs');
$I->makeScreenshot('success-pages-index');