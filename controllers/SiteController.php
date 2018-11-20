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
        $secret = Yii::$app->params['github_update_secret'];
        $headers = getallheaders();
        $hubSignature = $headers['X-Hub-Signature'];
        // Split signature into algorithm and hash
        list($algo, $hash) = explode('=', $hubSignature, 2);

        // Get payload
        $payload = file_get_contents('php://input');

        // Calculate hash based on payload and the secret
        $payloadHash = hash_hmac($algo, $payload, $secret);

        // Check if hashes are equivalent
        if ($hash !== $payloadHash) {
            // Kill the script or do something else here.
            die();
        }else{
            Yii::info("update ok");
        }

        // Your code here.
        $data = json_decode($payload);
        system(sprintf("cd %s;git pull", Yii::getAlias("@app")));
    }
}
