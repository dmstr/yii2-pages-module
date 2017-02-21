<?php
// @group mandatory

class ApplicationTest extends \Codeception\Test\Unit
{
    // tests
    public function testApp()
    {
        $this->assertNotEquals(null,Yii::$app);
    }

    public function testRequest()
    {
        $this->assertNotEquals(null,Yii::$app->request);
    }
}
