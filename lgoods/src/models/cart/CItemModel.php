<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-11-25
 * Time: 下午3:10
 */
namespace lgoods\models\cart;

use lgoods\models\goods\GoodsModel;
use yii\base\Model;

class CItemModel extends Model{
    public function createItem($postData){
        $cartItem = new CartItem();
        $sku = GoodsModel::findSku()->where(['sku_id' => $postData['ci_sku_id']])->one();
        if(!$sku){
            $this->addError("", "指定的商品不存在");
            return false;
        }
        if(!$cartItem->load($postData, '') || !$cartItem->validate()){
            $this->addErrors($cartItem->getErrors());
            return false;
        }
        $cartItem->ci_status = CartItem::STATUS_VALID;
        $cartItem->ci_g_id = $sku['sku_g_id'];
        $cartItem->insert(false);
        return $cartItem;
    }
    public static function findFull($params = []){
        $query = CartItem::find();
        return $query;
    }
    public static function formatOne($data, $params = []){
        return $data;
    }
}