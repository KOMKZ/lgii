<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-28
 * Time: 上午9:22
 */
namespace luser\models\user;

use lbase\staticdata\ConstMap;
use lfile\models\FileModel;
use luser\models\user\UserModel;
use Yii;
use lbase\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\filters\RateLimitInterface;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface, RateLimitInterface
{
    const STATUS_ACTIVE = 'active';

    const STATUS_NO_AUTH = 'not_auth';

    const STATUS_LOCKED = 'locked';

    const NOT_AUTH = 'not_auth';

    const HAD_AUTH = 'had_auth';

    public $password;

    public $password_confirm;

    public $rememberMe = false;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'u_created_at',
                'updatedAtAttribute' => 'u_updated_at'
            ]
        ];
    }



    public function fields(){
        $fields = parent::fields();
        ArrayHelper::removeValue($fields, 'u_password_hash');
        ArrayHelper::removeValue($fields, 'u_auth_key');
        ArrayHelper::removeValue($fields, 'u_password_reset_token');
        ArrayHelper::removeValue($fields, 'u_access_token');
        $fields['user_extend'] = 'user_extend';
        $fields['u_avatar_url1'] = 'u_avatar_url1';
        $fields['u_avatar_url2'] = 'u_avatar_url2';
        $fields['u_role_name'] = 'u_role_name';


        return $fields;
    }

    public function releaseFields(){
        return [
            "user_extend"
        ];
    }

    public function scenarios(){
        return [
            'default' => [
                'u_username', 'u_email', 'u_status', 'u_auth_status', 'password', 'u_access_token', 'password', 'password_confirm'
            ],
            'create' => [
                'u_username', 'u_email', 'u_status', 'u_auth_status', 'password', 'u_access_token', 'password', 'password_confirm'
            ]
            ,'update' => [
                'u_status', 'u_auth_status', 'password', 'password_confirm'
            ]
            ,'login' => [
                'u_email', 'password', 'rememberMe'
            ]
        ];
    }

    public function getU_role_name(){
        $roles = array_keys(Yii::$app->authManager->getRolesByUser($this->u_id));
        return $roles;
    }

    public function getUser_extend(){
        return $this->hasOne(UserExtend::className(), ['u_id' => 'u_id'])
            ->select([
                'u_ext_id',
                'u_id',
                'u_avatar_id1',
                'u_avatar_id2'
            ]);
    }

    public function getU_avatar_url1(){
        if(!$this->user_extend->u_avatar_id1){
            return '';
        }
        return FileModel::buildFileUrlFromArr(FileModel::parseQueryId($this->user_extend->u_avatar_id1));
    }

    public function getU_avatar_url2(){
        if(!$this->user_extend->u_avatar_id2){
            return '';
        }
        return FileModel::buildFileUrlFromArr($this->user_extend->u_avatar_id2);
    }

    public function rules(){
        return [
            ['rememberMe', 'in', 'range' => [1, 0]],
            ['rememberMe', 'default', 'value' => 1],

            ['u_username', 'required'],
            ['u_username', 'match', 'pattern' => '/[a-zA-Z0-9_\-]/'],
            ['u_username', 'string', 'min' => 5, 'max' => 30],
            ['u_username', 'unique', 'targetClass' => self::className()],

            ['u_email', 'required'],
            ['u_email', 'email'],
            ['u_email', 'string', 'min' => 5, 'max' => 30],
            ['u_email', 'unique', 'targetClass' => self::className(), 'on' => ['create', 'update']],

            ['u_status', 'required'],
            ['u_status', 'in', 'range' => ConstMap::getConst('u_status', true)],

            ['u_auth_status', 'default', 'value' => User::STATUS_NO_AUTH],
            ['u_auth_status', 'in', 'range' => ConstMap::getConst('u_auth_status', true)],

            ['password', 'required', 'on' => ['create', 'login']],
            ['password', 'required', 'on' => 'update', 'skipOnEmpty' => true],
            ['password', 'string', 'min' => 6, 'max' =>  50],

            ['u_access_token', 'default', 'value' => ''],


            ['password_confirm', 'required', 'on' => 'create'],
            ['password_confirm', 'required', 'on' => 'update', 'skipOnEmpty' => true],
            ['password_confirm', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    /**
     * Returns the maximum number of allowed requests and the window size.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     * and the second element is the size of the window in seconds.
     */
    public function getRateLimit($request, $action){
        // todo
        return static::getDefaultRateLimit();
    }

    public static function getDefaultRateLimit(){
        $limiter = Yii::$app->params['api_behaviors']['rateLimiter'];
        return [$limiter['rateLimit'], $limiter['rateLimitPer']];
    }

    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     * and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($request, $action){
        list($max, $peroid) = static::getDefaultRateLimit();
        $user = Yii::$app->user->identity;
        $userData = $user->user_data;
        if(!$userData){
            $userData = (new UserModel())->createUserDataFormUser($user, [
                'u_remain_time' => $max,
                'u_last_timestamp' => time()
            ]);
            if(!$userData){
                return [0, time()];
            }
        }
        if($userData->u_remain_time == 0){
            list($max, $period) = [$max, $peroid];
            if((time() - $userData->u_last_timestamp) >= $period){
                return [$max, time()];
            }
        }
        return [$userData->u_remain_time, time()];
    }

    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @param int $allowance the number of allowed requests remaining.
     * @param int $timestamp the current timestamp.
     */
    public function saveAllowance($request, $action, $allowance, $timestamp){
        // todo
        $user = Yii::$app->user->identity;
        if($userData = $user->user_data){
            $userData->u_remain_time = $allowance - 1;
            $userData->u_last_timestamp = $timestamp;
            $userData->update(false);
        }
    }

    public static function tableName(){
        return "{{%user}}";
    }

    public static function findIdentity($id)
    {
        return UserModel::findActive()->andWhere(['=', 'u_id', $id])->one();
    }



    public static function findIdentityByAccessToken($token, $type = null)
    {
        try {
            $payload = UserModel::parseAccessToken($token, $type);
            $user = UserModel::findActive()->andWhere(['=', 'u_email', $payload->data->user_info->u_email])->one();
            if($user->u_access_token == $payload->jti){
                return $user;
            }
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUser_data(){
        return $this->hasOne(UserData::class, ['u_id' => 'u_id']);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['u_username' => $username, 'u_status' => self::STATUS_ACTIVE]);
    }

    public static function findByPasswordResetToken($token)
    {
//        if (!static::isPasswordResetTokenValid($token)) {
//            return null;
//        }
        return static::findOne([
            'u_password_reset_token' => $token,
            'u_status' => self::STATUS_ACTIVE,
        ]);
    }
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->u_auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}