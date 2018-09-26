<?php
namespace common\models\pay\billparser;

use Yii;
use yii\base\Object;
use yii\base\InvalidArgumentException;
use common\helpers\FileHelper;
use League\Csv\Reader;

/**
 *
 */
class WxBillParser extends Object{
    const TYPE_CSV = 'csv';
    public function parseTrade($data, $callback, $type = self::TYPE_CSV){
        switch ($type) {
            case self::TYPE_CSV:
                $this->parseTradeFromCsv($data, $callback);
                break;
            default:
                throw new InvalidArgumentException(Yii::t("app", "无效的参数{$type}"));
                break;
        }
    }
    public function parseTradeFromCsv($csvFile, $callback){
        $detailReader = "";
        $detailReader = Reader::createFromPath($csvFile);
        if(!$detailReader){
            return false;
        }

        $fieldsMap = [
            'created_at' => "交易时间",
            'appid' => "公众账号ID",
            'mch_id' => "商户号",
            'sub_mch_id' => "子商户号",
            'device_info' => "设备号",
            'trade_no' => "微信订单号",
            'out_trade_no' => "商户订单号",
            'openid' => "用户标识",
            'trade_type' => "交易类型",
            'trade_status' => "交易状态",
            'bank_type' => "付款银行",
            'fee_type' => "货币种类",
            'total_amount' => "总金额",
            'cmpy_gift_amount' => "企业红包金额",
            'refund_no' => "微信退款单号",
            'out_refund_no' => "商户退款单号",
            'refund_amount' => "退款金额",
            'cmpy_refund_ammount' => "企业红包退款金额",
            'refund_type' => "退款类型",
            'refund_status' => "退款状态",
            'goods_title' => "商品名称",
            'out_params' => "商户数据包",
            'service_amount' => "手续费",
            'service_amount_rate' => "费率",
        ];




        foreach($detailReader as $index => $row){
            try {
                $row = array_combine(array_keys($fieldsMap), array_map(function($val){
                    return trim($val, "`\s");
                }, $row));
            } catch (\Exception $e) {
                Yii::error($e);
                continue;
            }
            $row['created_at'] = strtotime($row['created_at']);
            // $row['completed_at'] = strtotime($row['completed_at']);
            // if('交易' == $row['bill_type']){
            //     $row['bill_type'] = 'trade';
            // }elseif('退款' == $row['bill_type']){
            //     $row['bill_type'] = 'refund';
            // }
            $row['total_amount'] = $row['total_amount'] * 100;
            // $row['receipt_amount'] = $row['receipt_amount'] * 100;
            $row['service_amount'] = $row['service_amount'] * 100;
            // $params['third_app_id'] = $trades['account'];
            list($func, $params) = $callback;
            call_user_func_array($func, [$row, $index, $params]);
        }
    }

}
