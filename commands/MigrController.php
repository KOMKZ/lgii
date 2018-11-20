<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Post;
use yii\helpers\ArrayHelper;

class MigrController extends Controller{
    public function actionReinstall(){
        $app = Yii::getAlias('@app');
        system(sprintf(
            "cd %s;" .
            "./yii migrate/down 100 --migration-path=@lgoods/migrations --interactive=0;" .
            "./yii migrate/down 100 --migration-path=@lfile/migrations --interactive=0;" .
            "./yii migrate/down 100 --interactive=0;" .
            "./yii migrate/up --migration-path=@lgoods/migrations --interactive=0;" .
            "./yii migrate/up --migration-path=@lfile/migrations --interactive=0;" .
            "./yii migrate/up --interactive=0;"
        , $app));
    }
}