<?php

namespace dmstr\modules\pages\tests\codeception\_pages;



/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class LoginPage
{
    public $route = 'user/security/login';

    /**
     * @param object $actor
     * @param string $username
     * @param string $password
     */
    public function login($actor, $username, $password)
    {
        $actor->fillField('input[name="login-form[login]"]', $username);
        $actor->fillField('input[name="login-form[password]"]', $password);
        $actor->click('button[type=submit]');
        $actor->wait(3);
    }
}
