<?php
namespace lgoods\models\refund;

use lgoods\models\order\OrderModel;
use lgoods\models\trans\Trans;
use Yii;
use lgoods\models\goods\GoodsModel;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use lgoods\models\order\AfterPayedEvent;

class RfModel extends Model{

    public function createRefund($data){
        $order = OrderModel::findOrderFull()
                            ->andWhere(['=','od_num', $data['od_num']])
                            ->asArray()
                            ->one();
        if(!$order){
            throw new \Exception("指定的订单不存在");
        }
        list($canRefund, $reason) = OrderModel::ensureCanRefund($order);
        if(!$canRefund){
            $this->addError("", $reason);
            return false;
        }

        $validOgList = static::fetchOgList($order['order_goods_list']);
        list($error, $message) = static::checkRfOgList($data['og_rf_goods_list'], $validOgList);
        if($error){
            $this->addError("", $message);
            return false;
        }
        $rf = new RfApplication();
        $rf->rf_order_id = $order['od_id'];
        $rf->rf_order_num = $order['od_num'];
        $rf->rf_order_third_num = $order['trs_pay_num'];
        $rf->rf_order_trs_num = $order['trs_num'];
        $rf->rf_status = RfApplication::STATUS_SUBMIT;
        $rf->rf_ori_pay_type = $order['od_pay_type'];
        $rf->rf_num = static::buildRfNumber();
        $rf->insert(false);
        $rfOgList = static::buildRfOgList($rf, $data['og_rf_goods_list'], $validOgList);
        static::batchInsertRgOgList($rfOgList);
        $rf->rf_title = static::buildRfTitleFromGoods($rfOgList);
        $rf->rf_fee = static::caculateRfFee($rf, $rfOgList, []);
        $rf->update(false);

        return $rf;

    }

    public static function buildRfTitleFromGoods($rfOgList){
        return count($rfOgList) > 1 ?
            sprintf("退款-%s等%s件商品", $rfOgList[0]['rg_name'], count($rfOgList))
            :
            sprintf("退款-%s 1件商品", $rfOgList[0]['rg_name']);
    }

    public static function buildRfNumber(){
        list($time, $millsecond) = explode('.', microtime(true));
        $string = sprintf("RF%s%04d", date("HYisdm", $time), $millsecond);
        return $string;
    }

    public static function caculateRfFee($rf, $rfOgList, $params = []){
        $totalFee = 0;
        foreach($rfOgList as $rfGoods){
            $totalFee += $rfGoods['rg_total_price'];
        }
        return $totalFee;
    }

    public static function buildRfOgList($rf, $targetRfOgList, $validOgList){
        $validOgList = ArrayHelper::index($validOgList, 'og_id');
        $list = [];
        foreach($targetRfOgList as $item){
            $orderGoods = $validOgList[$item['og_id']];
            $rgItem = [
                'rg_og_id' => $orderGoods['og_id'],
                'rg_rf_id' => $rf['rf_id'],
                'rg_total_num' => $orderGoods['og_total_num'],
                'rg_single_price' => $orderGoods['og_single_price'],
                'rg_total_price' => $orderGoods['og_total_price'],
                'rg_name' => $orderGoods['og_name'],
                'rg_g_id' => $orderGoods['og_g_id'],
                'rg_g_sid' => $orderGoods['og_g_sid'],
                'rg_g_stype' => $orderGoods['og_g_stype'],
                'rg_sku_id' => $orderGoods['og_sku_id'],
                'rg_sku_index' => $orderGoods['og_sku_index'],
                'rg_created_at' => time(),
                'rg_updated_at' => time(),
            ];
            ksort($rgItem);
            $list[] = $rgItem;
        }
        return $list;
    }

    public static function batchInsertRgOgList($rfOgList){
        return Yii::$app->db->createCommand()->batchInsert(RfGoods::tableName(), [
            'rg_created_at',
            'rg_g_id',
            'rg_g_sid',
            'rg_g_stype',
            'rg_name',
            'rg_og_id',
            'rg_rf_id',
            'rg_single_price',
            'rg_sku_id',
            'rg_sku_index',
            'rg_total_num',
            'rg_total_price',
            'rg_updated_at',
        ], $rfOgList)->execute();
    }

    public function agreeRefund($rf){
        list($canRf, $reason) = static::ensureRefundCanAgree($rf);
        if(!$canRf){
            $this->addError("", $reason);
            return false;
        }
        $rf->rf_status = RfApplication::STATUS_HAD_REFUND;
        $rf->update(false);

        return $rf;
    }

    public static function ensureRefundCanAgree($rf){
        if($rf['rf_status'] == RfApplication::STATUS_SUBMIT){
            return [true, ""];
        }
        return [false, "非提交申请状态"];
    }

    public static function checkRfOgList($targetRfList, $validOgList){
        $list = ArrayHelper::index($validOgList, 'og_id');
        $result = [
            false,
            '',
        ];
        foreach($targetRfList as $goods){
            if(!array_key_exists($goods['og_id'], $list)){
                $result[0] = true;
                $result[1] = sprintf("指定的商品%s不存在", $goods['og_id']);
                return $result;
            }
        }
        return $result;
    }

    public static function fetchOgList($ogList){
        $list = [];
        foreach($ogList as $orderGoods){
            $list[] = $orderGoods;
        }
        return $list;
    }
}