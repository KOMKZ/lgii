<?php
namespace luser\controllers;

use Yii;
use lbase\Controller;
use luser\models\user\UserModel;
use lbase\filters\HttpBearerAuth;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use luser\models\user\User;

/**
 *
 */
class LuserController extends Controller{
    public function actionUpdate($index){
        $postData = Yii::$app->request->getBodyParams();
        $uTab = User::tableName();
        $user = UserModel::findSafeField()->andWhere(["{$uTab}.u_id" => $index])->one();
        if(!$user){
            return $this->notfound();
        }
        $uModel = new UserModel();
        $result = $uModel->updateUser($user, $postData);
        if(!$result){
            return $this->error(null, $uModel->getErrors());
        }
        return $this->succ($user->toArray());
    }

    public function actionView($index){
        $uTab = User::tableName();
        $user = UserModel::findSafeField()
            ->andWhere(["{$uTab}.u_id" => $index])
            ->one();

        if(!$user){
            return $this->notfound();
        }
        return $this->succ($user->toArray());
    }

    public function actionList(){
        $getData = Yii::$app->request->get();
        $defaultOrder = [
            'u_created_at' => SORT_DESC,
            'u_updated_at' => SORT_DESC
        ];
        $query = UserModel::findSafeField();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $defaultOrder,
                'attributes' => [
                    'u_created_at',
                    'u_updated_at'
                ]
            ]
        ]);
        return $this->succItems($provider->getModels(), $provider->totalCount);
    }
    public function actionCreate(){
        $postData = Yii::$app->request->getBodyParams();
        $uModel = new UserModel();
        $user = $uModel->createUser($postData);
        if(!$user){
            return $this->error(null, $uModel->getErrors());
        }
        return $this->succ($user->toArray());
    }
}
