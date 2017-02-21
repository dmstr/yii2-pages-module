<?php
// @group mandatory
$I = new CliTester($scenario);
$I->runShellCommand('yii copy-pages/root-node 1 ru');
$I->seeInShellOutput('"root_de" successfully copied to language "ru"');
