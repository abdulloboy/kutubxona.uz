<?php

use yii\db\Schema;
use yii\db\Migration;
use common\models\User;

class m160106_164351_RBAC extends Migration
{
    public function up()
    {
        $this->initRBAC();
        $this->initUser();
    }

    public function down()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();
        
        return true;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
    
    public function initRBAC()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        $reader = $auth->createRole('reader');
        $auth->add($reader);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $reader);
        
        return "OK";
    }
    
    public function initUser()
    {
        $user=User::findByUsername('admin');
        if(!$user){
            $user=new User;
            $user->name='admin';
            $user->email='ebilim@mail.ru';
            $user->setPassword('admin5');
            $user->generateAuthKey();
            if (!$user->save()){
            echo "Can't save admin";
            return;
        }
        }
        
        $auth = Yii::$app->authManager;
        $admin=$auth->getRole('admin');
        $auth->assign($admin, $user->getId());           
    }
    
}
