<?php
namespace app\commands;

use luser\models\user\User;
use luser\models\user\UserModel;
use Yii;
use yii\console\Controller;
use yii\console\controllers\MigrateController;


class SiteController extends Controller{
    public function instInitData(){
        // 用户数据
        Yii::$app->db->createCommand()->truncateTable(User::tableName())->execute();
        $default = [
            'u_username' => 'admin',
            'password' => '123456',
            'password_confirm' => '123456',
            'u_email' => '784248377@qq.com',
            'u_auth_status' => 'had_auth',
            'u_status' => 'active',
        ];
        $uModel = new UserModel();
        $uModel->createUser($default);

        $default = [
            'u_username' => 'user01',
            'password' => '123456',
            'password_confirm' => '123456',
            'u_email' => '784248378@qq.com',
            'u_auth_status' => 'had_auth',
            'u_status' => 'active',
        ];
        $uModel = new UserModel();
        $uModel->createUser($default);
    }
    public function actionInstall(){
        $app = Yii::$app;
        $app->runAction('migrate/down', [
            "1000",
            'interactive' => 0
        ]);
        $app->runAction("migrate/up", [
            'interactive' => 0
        ]);
        $this->instInitData();
        $app->runAction("rbac/gene-rbac-data");
        $app->runAction("rbac/install-rbac-data");
    }
    public function actionRunTest(){
        $testBin = Yii::getAlias("@app/vendor/codeception/base/codecept");
        system(sprintf("%s run api", $testBin));
    }
}