<?php
namespace lbase;

use lsite\models\set\SetModel;
use Yii;
use yii\web\Controller as BaseController;
/**
 *
 */
class Controller extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors(){
        $behaviors = SetModel::get("api_behaviors");
        foreach(SetModel::get("api_behaviors_bootstrap") as $name => $ok){
            if(!$ok){
                unset($behaviors[$name]);
            }
        }
        if(array_key_exists('bearerAuth', $behaviors)){
            $ignoreRoutes = array_keys(Yii::$app->authManager->getPermissionsByRole('vistor'));
            foreach($ignoreRoutes as $name){
                $behaviors['bearerAuth']['optional'][] = $name;
            }
        }
        return $behaviors;
    }
    private function getRes(){
        return [
            'code' => null,
            'data' => null,
            'message' => null
        ];
    }
    public function checkDataIsArray($data){
        foreach($data as $key => $value){
            if(!is_numeric($key)){
                return false;
            }
        }
        return true;
    }

    public function notfound($error = null){
        return $this->error(404, $error ? $error : Yii::t('app', '数据不存在'));
    }
    public function errorParams($error = null){
        return $this->error(500, $error ? $error : Yii::t('app', '参数错误'));
    }

    public function succItems($items, $count = null){
        $res = $this->getRes();
        $res['data'] = [
            'count' => null === $count ? count($items) : $count,
            'items' => $items,
        ];
        $res['code'] = 0;
        $res['message'] = '';
        return $res;
    }
    public function succ($data = null){
        $res = $this->getRes();
        $res['data'] = $data;
        $res['code'] = 0;
        $res['message'] = '';
        return $res;
    }
    public function error($code, $message){
        $res = $this->getRes();
        $res['data'] = null;
        $res['code'] = empty($code) ? 1 : $code;
        $res['message'] = $message;
        return $res;
    }

    public function beginTransaction(){
        return Yii::$app->db->beginTransaction();
    }
}
