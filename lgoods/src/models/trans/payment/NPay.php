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

    public function createOrder($data, $type){
        return [
            'master_data' => "npay dont have master data",
            'response' => "npay dont have reponse",
        ];
    }

    public function getThirdTransId($payOrder){
        return 'npay dont dont have thrid id';
    }

}