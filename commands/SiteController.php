<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\controllers\MigrateController;


class SiteController extends Controller{
    public function actionInstall(){
        $app = Yii::$app;
        $app->runAction('migrate/down', [
            "1000",
            'interactive' => 0
        ]);
        $app->runAction("migrate/up", [
            'interactive' => 0
        ]);
    }
    public function actionRunTest(){
        $testBin = Yii::getAlias("@app/vendor/codeception/base/codecept");
        system(sprintf("%s run api", $testBin));

    }
}