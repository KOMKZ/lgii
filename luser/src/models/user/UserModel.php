<?php
namespace luser\models\user;

use Yii;
use yii\base\Model;
use luser\models\user\User;
use luser\models\user\UserExtend;
use luser\models\user\UserData;
use lbase\filters\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use lsite\models\set\SetModel;
use Firebase\JWT\JWT;
use yii\helpers\ArrayHelper;
use common\models\trans\ar\Transaction;
use luser\models\user\UserBillRecord;
use luser\models\user\UserReceiverAddr;
use common\models\user\query\UserReceAddrQuery;
use lfile\models\FileModel;
use common\models\file\query\FileQuery;
/**
 *
 */
class UserModel extends Model
{
    public static function find(){
        return User::find();
    }

    public static function findSafeField(){
        $uTab = User::tableName();
        $uExtTab = UserExtend::tableName();
        $query = static::find()->select([
            // base
            "{$uTab}.u_auth_status",
            "{$uTab}.u_created_at",
            "{$uTab}.u_email",
            "{$uTab}.u_id",
            "{$uTab}.u_status",
            "{$uTab}.u_updated_at",
            "{$uTab}.u_username",
            // extend
            "{$uExtTab}.u_ext_id",
            "{$uExtTab}.u_avatar_id1",
            "{$uExtTab}.u_avatar_id2"
        ]);
        $query->joinWith('user_extend');
        return $query;
    }

    public static function findActive(){
        return static::find()
            ->where(['u_status' => User::STATUS_ACTIVE]);
    }

	/**
	 * 用户所属交易成功支付响应处理
	 * @param  [type] $event [description]
	 * @return [type]        [description]
	 */
	public static function handleReceivePayedEvent($event){
		$trans = $event->sender;
		$user = $event->belongUser;
		// 插入用户账单
		static::createUserBill($user, $trans);
	}

	/**
	 *
	 * @param  [type] $user     [description]
	 * @param  [type] $addrData [description]
	 * - rece_name: string, required
	 * - rece_contact_number: string,  required
	 * - rece_location_id: string, required
	 * - rece_location_string: string, required
	 * - rece_tag: string, optional 默认为空
	 * - rece_default_addr: string, optional yes|no 默认为yes,如果没有数据的话, 如果有数据则是no
     *
	 */
	public function createUserReceiverAddr($user, $addrData){
		$receiverAddr = new UserReceiverAddr();
		if(!$receiverAddr->load($addrData, '') || !$receiverAddr->validate()){
			$this->addError('', $this->getOneErrMsg($receiverAddr));
			return false;
		}
		$receiverAddr->rece_belong_uid = $user->u_id;
		$hasDefaultAddr = UserReceAddrQuery::find()->where(['rece_belong_uid' => $user->u_id])->count();
		$receiverAddr->rece_default_addr = $hasDefaultAddr ? 'no' : "yes";
        $receiverAddr->insert(false);
		return $receiverAddr;
	}

	public static function createUserBill(User $user, Transaction $trans){
		$billData = [
			'u_id' => $user->u_id,
			'u_bill_type' => $trans->t_type,
			'u_bill_fee' => $trans->t_fee,
			'u_bill_fee_type' => $trans->t_fee_type,
			'u_bill_related_id' => $trans->t_app_no,
			'u_bill_related_type' => $trans->t_module,
			'u_bill_trade_no' => $trans->t_number,
			'u_bill_created_at' => time()
		];
		return Yii::$app->db->createCommand()
							->insert(UserBillRecord::tableName(), $billData)
							->execute();
	}

	public function createUserDataFormUser(User $user, $data){
		$userData = new UserData();
		if(!$userData->load($data, '') || !$userData->validate()){
			$this->addErrors($userData->getErrors());
			return false;
		}
		$userData->u_id = $user->u_id;
        $userData->insert(false);
		return $userData;
	}

	/**
	 * [createUser description]
	 * @param  [type] $data [description]
	 * - u_username String, required
	 * - password String, required
	 * - password_confirm String, required
	 * - u_email String, required
	 * - u_auth_status String
	 * - u_status String
	 * @return [type]       [description]
	 */
	public function createUser($data){
		$t = Yii::$app->db->beginTransaction();
		try {
			// 基础数据
			$user = new User();
            $user->scenario = "create";
			if(!$user->load($data, '') || !$user->validate()){
				$this->addErrors($user->getErrors());
				return false;
			}
			$user->u_auth_key = User::NOT_AUTH == $user->u_auth_status ?
								static::buildAuthKey() : '';
			$user->u_status = User::NOT_AUTH == $user->u_auth_status ?
								User::STATUS_NO_AUTH : $user->u_status;
			$user->u_password_hash = static::buildPasswordHash($user->password);
			$user->u_password_reset_token = '';
            $user->insert(false);
			// 扩展数据
			$userExtend = new UserExtend();
			if(!$userExtend->load($data, '') || !$userExtend->validate()){
				$this->addErrors($userExtend->getErrors());
				return false;
			}
			$userExtend->u_id = $user->u_id;
            $userExtend->insert(false);
			$t->commit();
			return $user;
		} catch (\Exception $e) {
		    $t->rollBack();
            throw $e;
		}
	}



	public function updateUser(User $user, $data){
		$user->scenario = 'update';
		if(!$user->load($data, '') || !$user->validate()){
			$this->addErrors($user->getErrors());
			return false;
		}
		if($user->password){
			$user->u_password_hash = static::buildPasswordHash($user->password);
		}
		if(false === $user->update(false)){
			$this->addError("", Yii::t('app', "数据库更新失败"));
			return false;
		}
		$userExtend = $user->user_extend;
		// todo 修改为永久文件的逻辑可以考虑使用异步
		$oldAvatar1 = $userExtend->u_avatar_id1;
		if(!$userExtend->load($data, '') || !$userExtend->validate()){
			$this->addErrors($user->getErrors());
			return false;
		}
		if($oldAvatar1 != $userExtend->u_avatar_id1){
			$newfileInfo = FileModel::parseQueryId($userExtend->u_avatar_id1);
			$oldFileInfo = FileModel::parseQueryId($oldAvatar1);
			$newFile = FileQuery::find()->andWhere($newfileInfo)->one();
			if($newFile){
				$newFile->file_is_tmp = 0;
				$newFile->update(false);
			}
			$oldFile = FileQuery::find()->andWhere($oldFileInfo)->one();
			if($oldFile){
				$oldFile->file_is_tmp = 1;
				$oldFile->update(false);
			}
		}
        $userExtend->update(false);
		return $user;
	}


	public function validatePassword($user, $password){
		return Yii::$app->security->validatePassword($password, $user->u_password_hash);
	}

	// todo 出现多种的时候考虑分离成类来处理
	public static function parseAccessToken($token, $type){
        try {
			switch ($type) {
				case HttpBearerAuth::class:
					$payload = JWT::decode($token, SetModel::get('jwt.secret_key'), SetModel::get('jwt.allow_algs'));
                    break;
				default:
					throw new ForbiddenHttpException();
					break;
			}
			return $payload;
		} catch (\Exception $e) {
			throw $e;
		}
	}

	// todo 出现多种的时候考虑分离成类来处理
	public static function buildToken($data, $type){
		try {
			$token = null;
			switch ($type) {
				case HttpBearerAuth::class:
					$issuedAt   = time();
					$notBefore  = $issuedAt + 5;             //Adding 10 seconds
					$expire     = ArrayHelper::getValue($data, 'token_info.expire', $notBefore + 7200);            // Adding 60 seconds
					$serverName = ArrayHelper::getValue($data, 'token_info.server_name', Yii::$app->id); // Retrieve the server name from config file
					$data = [
						'iat'  => $issuedAt,         // Issued at: time when the token was generated
						'jti'  => ArrayHelper::getValue($data, 'token_info.id', base64_encode(mcrypt_create_iv(32))),//base64_encode(mcrypt_create_iv(32)),          // Json Token Id: an unique identifier for the token
						'iss'  => $serverName,       // Issuer
						'nbf'  => $notBefore,        // Not before
						'exp'  => $expire,           // Expire
						'data' => $data
					];
					$token = \Firebase\JWT\JWT::encode(
							$data,      //Data to be encoded in the JWT
							SetModel::get('jwt.secret_key'), // The signing key
							SetModel::get('jwt.encode_alg')     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
							);
					break;
				default:
					break;
			}
			return $token;
		} catch (\Exception $e) {
            throw $e;
		}
	}
	public static function handleAfterLogout($event){
		static::updateUserAccessToken($event->identity, '');
	}

	public function loginInSession($user, $remember = false){
		Yii::$app->user->login($user,  3600 * 24 * 30);
		return true;
	}

	public function loginInAccessToken($user, $accessToken){
		static::updateUserAccessToken($user, $accessToken);
		Yii::$app->user->login($user);
		return true;
	}

	public static function updateUserAccessToken(User $user, $token){
		$user->u_access_token = $token;
		return $user->update(false);
	}

	protected static function buildPasswordHash($password){
		return Yii::$app->security->generatePasswordHash($password);;
	}
	public static function buildAccessToken(){
		return base64_encode(mcrypt_create_iv(32));
	}
	protected static function buildAuthKey(){
		return Yii::$app->security->generateRandomString();
	}


}
