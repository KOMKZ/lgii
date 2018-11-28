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

    /**
     * @api post,/luser,User,创建用户
     * - u_username required,string,in_body,用户昵称
     * - password required,string,in_body,密码
     * - password_confirm required,string,in_body,确认密码
     * - u_email required,string,in_body,邮箱
     * - u_auth_status required,string,in_body,验证状态
     * - u_status required,string,in_body,状态
     *
     * @return #global_res
     * - data object#user_item,返回用户信息
     *
     */
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

/**
 *
 * @def #user_item
 * - u_username string,用户名称
 * - u_email string,用户邮箱
 * - u_auth_status string,验证状态
 * - u_status string,用户状态
 * - u_created_at integer,创建时间
 * - u_updated_at integer,更新时间
 * - u_id integer,用户id
 * - u_avatar_url1 string,用户url
 * - u_avatar_url2 string,用户url
 * - u_role_name array#string,角色列表
 *
 */