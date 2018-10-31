<?php
namespace lgoods\models\trans\payment;

use Yii;
use yii\base\Model;
/**
 *
 */
class NPay extends Model{
    CONST NAME = 'npay';
    CONST MODE_ALL = 'all';
    public $id = "npay";

    public function createOrder($data, $type){
        return [
            'master_data' => "",
            'response' => "",
        ];
    }
    public function createRefund($data){
        return [""];
    }
    public function getThirdTransId($payOrder){
        return '';
    }

}