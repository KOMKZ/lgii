<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-11-25
 * Time: 下午3:10
 */
namespace lgoods\models\cart;

use lgoods\models\sale\SaleModel;
use Yii;
use lbase\helpers\ArrayHelper;
use lfile\models\FileModel;
use lgoods\models\goods\Goods;
use lgoods\models\goods\GoodsExtend;
use lgoods\models\goods\GoodsModel;
use lgoods\models\goods\GoodsSku;
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
        $query->from(['c' => CartItem::tableName()]);
        $query->leftJoin(['ge' => GoodsExtend::tableName()], "c.ci_g_id = ge.g_id");
        $query->leftJoin(['g' => Goods::tableName()], "c.ci_g_id = g.g_id");
        $query->leftJoin(['sku' => GoodsSku::tableName()], "c.ci_sku_id = sku.sku_id");
        $query->select([
            "c.ci_id",
            "c.ci_g_id",
            "c.ci_sku_id",
            "c.ci_amount",
            "c.ci_belong_uid",
            "ge.g_m_img_id",
            "g.g_name",
            "sku.sku_price",
            "sku.sku_name"
        ]);
        return $query;
    }

    public static function formatList($items, $parmas = []){
        foreach($items as &$item){
            $item = static::formatOne($item, $parmas);
        }
        return $items;
    }
    public static function formatOne($item, $params = []){
        $item['g_m_img_url'] = Yii::$app->file->buildFileUrlStatic(FileModel::parseQueryId(ArrayHelper::getValue($item, 'g_m_img_id', '')));
        if(ArrayHelper::getValue($params, 'with_price', 1)){
            $buyParams['discount_items'] = SaleModel::fetchGoodsRules([
                'g_id' => $item['ci_g_id'],
                'sku_id' => $item['ci_sku_id']
            ]);
            $buyParams['buy_num'] = $item['ci_amount'];
            $buyParams['customer_uid'] = $item['ci_belong_uid'];
            $priceItem = GoodsModel::caculatePrice($item, $buyParams);
            $item['g_price'] = $priceItem['og_total_price'];
            $item['g_discount'] = $priceItem['og_total_discount'];
            $item['discount_items_des'] = $priceItem['discount_items_des'];
        }
        return $item;
    }

}