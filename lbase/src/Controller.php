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

    public function behaviors(){
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    // restrict access to
                    'Origin' => ["*"],
                    'Access-Control-Request-Method' => ['POST', 'PUT', 'GET', 'DELETE', 'OPTION'],
                    // Allow only POST and PUT methods
                    'Access-Control-Request-Headers' => ['X-Wsse'],
                    // Allow only headers 'X-Wsse'
                    'Access-Control-Allow-Credentials' => true,
                    // Allow OPTIONS caching
                    'Access-Control-Max-Age' => 3600,
                    // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                    'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                ],
            ],
        ];
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
