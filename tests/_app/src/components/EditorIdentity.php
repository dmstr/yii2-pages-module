<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2017 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\components;


use yii\web\IdentityInterface;

class EditorIdentity implements IdentityInterface
{
    public $username = 'editor';

    /**
     * @todo required for ActiveRecordAccessTrait
     */
    public $isAdmin = false;

    /**
     * @inheritdoc
     */
    public static function findIdentity($id){
        return \Yii::createObject(EditorIdentity::class);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null){
        return \Yii::createObject(EditorIdentity::class);

    }

    /**
     * @inheritdoc
     */
    public function getId(){
        return 1000;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {

    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {

    }
}