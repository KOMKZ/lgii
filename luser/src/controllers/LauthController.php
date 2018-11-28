<?php
namespace luser\controllers;

use Yii;
use lbase\Controller;
use luser\models\user\UserModel;
use lbase\filters\HttpBearerAuth;

/**
 *
 */
class LauthController extends Controller
{
    /**
     * @api post,/lauth,Auth,登录用户获取访问token
     * - u_email required,string,in_body,注册邮箱
     * - password required,string,in_body,密码
     * - type required,string,in_body,填入token即可
     *
     * @return #global_res
     * - data object#login_res,返回访问token
     *
     */
	public function actionLogin(){
		$post = Yii::$app->request->getBodyParams();
		if(empty($post['u_email']) || empty($post['password']) || empty($post['type'])){
			Yii::$app->response->statusCode = 401;
			return $this->error(401, Yii::t('app', "参数错误"));
		}
		$user = UserModel::findActive()->andWhere(['u_email' => $post['u_email']])->one();
		if(!$user){
			Yii::$app->response->statusCode = 401;
			return $this->error(401, Yii::t('app', "用户不存在/未激活"));
		}
		$uModel = new UserModel();
		if(!$uModel->validatePassword($user, $post['password'])){
			Yii::$app->response->statusCode = 401;
			return $this->error(401, Yii::t('app', "密码错误"));
		}
		$accessToken = UserModel::buildAccessToken();
		$expire = time() + 3600;
		$payload = [
			'user_info' => $user->toArray(),
			'token_info' => [
				'id' => $accessToken,
				'expire' => $expire
			]
		];
		$uModel->loginInAccessToken($user, $accessToken);
		$token = $uModel->buildToken($payload,  HttpBearerAuth::className());
		if(!$token){
			Yii::$app->response->statusCode = 401;
			return $this->error('401', Yii::t('app', "系统生成access-token失败"));
		}
		return $this->succ(['jwt' => $token]);
	}

	public function actionGetInfo(){
		$user = Yii::$app->user->identity;
		$userInfo = $user->toArray();
		$userInfo['u_role'] = "admin";
		return $this->succ($userInfo);
	}

	public function actionLogout(){
		Yii::$app->user->logout();
		return $this->succ(true);
	}

}
/**
 * @def #login_res
 * - jwt string,访问token
 */
