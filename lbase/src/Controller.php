<?php
namespace lbase;

use Yii;
use yii\web\Controller as BaseController;
/**
 *
 */
class Controller extends BaseController
{
    public $enableCsrfValidation = false;
    private function getRes(){
        return [
            'code' => null,
            'data' => null,
            'message' => null
        ];
    }


    public function notfound($error = null){
        return $this->error(404, $error ? $error : Yii::t('app', '数据不存在'));
    }
    public function succItems($items, $count = null){
        $res = $this->getRes();
        $res['data'] = [
            'items' => $items,
            'count' => null === $count ? count($items) : $count
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
