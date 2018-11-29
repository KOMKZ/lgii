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
    public function updateItem($citem, $data){
        if(!$citem->load($data, '') || !$citem->validate()){
            $this->addErrors($citem->getErrors());
            return false;
        }
        $citem->update(false);
        return $citem;
    }
    public function removeItem($citem){
        $citem->ci_status = CartItem::STATUS_DELETE;
        $citem->update(false);
        return $citem;
    }

    public static function adjustAmount($citem, $value){
        $citem->ci_amount += $value;
        $citem->ci_amount = $citem->ci_amount <= 0 ? 1 : $citem->ci_amount;
        $citem->update(false);
        return $citem;
    }
    public static function findFull($params = []){
        $query = CartItem::find()->andWhere(['=', 'ci_status', CartItem::STATUS_VALID]);
        return $query;
    }
    public static function formatList($items, $parmas = []){
        foreach($items as &$item){
            $item = static::formatOne($item, $parmas);
        }
        return $items;
    }
    public static function formatOne($item, $params = []){
        return $item;
    }
}