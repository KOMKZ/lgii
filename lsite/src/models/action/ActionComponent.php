<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 下午1:56
 */
namespace lsite\models\action;

use lfile\models\FileModel;
use luser\models\user\User;
use Yii;
use yii\base\Component;
use yii\base\Model;

class ActionComponent extends Component{
    public $flushInterval  = 20;
    public $flushAtOnce = false;
    protected $messages = [];
    public $actionNameFile = "@lsite/models/action/action_name.php";
    protected static $actionNames = [];
    public function init()
    {
        parent::init();
        register_shutdown_function([$this, 'flush']);
    }
    public static function getActionName($action){
        if(!static::$actionNames){
            static::$actionNames = require(Yii::getAlias(Yii::$app->alog->actionNameFile));
        }
        if(isset(static::$actionNames[$action])){
            return static::$actionNames[$action];
        }
        return "";
    }
    public static function formatList($list, $params = []){
        foreach($list as &$item){
            $item = static::formatOne($item);
        }
        return $list;
    }
    public static function formatOne($one){
        $one['al_action_name'] = static::getActionName($one['al_action']);
        return $one;
    }
    public static function find(){
        return ActionLog::find();
    }
    public static function findFull($params = []){
        $query = ActionLog::find();
        $query->from(['al' => ActionLog::tableName()]);
        $query->leftJoin(['u' => User::tableName()], "u.u_id = al.al_opr_uid")
              ->select([
                  'al.*',
                  'u.u_username as al_opr_uname'
              ])
              ->asArray();
        return $query;
    }
    public function log($name, ActionTargetInterface $target, $params = [], $uid = 0){
        $this->messages[] = [
            $name,
            time(),
            json_encode(array_merge($target->getLogParams($name), $params)),
            null,
            $target->getLogId(),
            $uid ? $uid : $this->getDefaultUid(),
            time()
        ];
        if($this->flushAtOnce || ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval)){
            $this->flush();
        }
    }
    public function getDefaultUid(){
        $iden = Yii::$app->user->getIdentity();
        return $iden ? $iden->getId() : 0;
    }
    public function flush(){
        $messages = $this->messages;
        $this->messages = [];
        if(!$messages){
            return null;
        }
        return Yii::$app->db->createCommand()->batchInsert(ActionLog::tableName(), [
            "al_action",
            "al_created_at",
            "al_data",
            "al_id",
            "al_obj_id",
            "al_opr_uid",
            "al_updated_at",
        ], $messages)->execute();
    }

}

