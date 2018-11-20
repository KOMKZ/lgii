<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use lbase\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    public function actionUpdate(){
        system(sprintf("cd %s;git pull", Yii::getAlias("@app")));
    }
}
