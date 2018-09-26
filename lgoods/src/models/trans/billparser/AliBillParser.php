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
class AliBillParser extends Object
{
    const TYPE_ZIP = 'zip';
    public function parseTrade($data, $callback, $type = self::TYPE_ZIP){
        switch ($type) {
            case self::TYPE_ZIP:
                $this->parseTradeFromZip($data, $callback);
                break;
            default:
                throw new InvalidArgumentException(Yii::t("app", "无效的参数{$type}"));
                break;
        }
    }
    public function parseTradeFromZip($zipFile, $callback){
        $fileDir = FileHelper::unzipFileToDir($zipFile, 'GBK');

        $detailReader = "";
        $summaryReader = "";
        foreach(glob($fileDir . '/*') as $filePath){
            $reader = Reader::createFromPath($filePath);
            $header = array_pop($reader->fetchOne(0));
            if('#支付宝业务汇总查询' == $header){
                $summaryReader = $reader;
            }elseif('#支付宝业务明细查询' == $header){
                $detailReader = $reader;
            }else{
                throw new \Exception("不支持的csv头{$header}");
            }
        }
        if(!$detailReader){
            return false;
        }

        // 解析detailreader
        $trades = [
            'account' => '',
            'start_at' => '',
            'end_at' => '',
            'data' => []
        ];
        if(preg_match('/#账号：\[([\d]+)\]/', array_pop($detailReader->fetchOne(1)), $matches)){
            $trades['account'] = $matches[1];
        }
        if(preg_match('/#起始日期：\[(.+)\]\s+终止日期：\[(.+)\]/', array_pop($detailReader->fetchOne(2)), $matches)){
            $trades['start_at'] = preg_replace('/日/u', '',
                                  preg_replace('/[年月]/u', '/', $matches[1])
                                  );
            $trades['end_at'] = preg_replace('/日/u', '',
                                preg_replace('/[年月]/u', '/', $matches[2])
                                );
        }
        $fieldsMap = [
            'trade_no' =>  "支付宝交易号",
            'out_trade_no' =>  "商户订单号",
            'bill_type' =>  "业务类型",
            'goods_title' =>  "商品名称",
            'created_at' =>  "创建时间",
            'completed_at' =>  "完成时间",
            'store_id' =>  "门店编号",
            'store_name' =>  "门店名称",
            'operator_id' =>  "操作员",
            'terminal_id' =>  "终端号",
            'target_account' =>  "对方账户",
            'total_amount' =>  "订单金额（元）",
            'receipt_amount' =>  "商家实收（元）",
            'point_amount00' =>  "支付宝红包（元）",
            'point_amount01' =>  "集分宝（元）",
            'point_amount02' =>  "支付宝优惠（元）",
            'point_amount03' =>  "商家优惠（元）",
            'field01' =>  "券核销金额（元）",
            'field02' =>  "券名称",
            'field03' =>  "商家红包消费金额（元）",
            'field04' =>  "卡消费金额（元）",
            'refund_no' =>  "退款批次号/请求号",
            'service_amount' =>  "服务费（元）",
            'field05' =>  "分润（元）",
            'comment' =>  "备注"
        ];
        foreach($detailReader as $index => $row){
            if($index > 5){
                try {
                    $row = array_combine(array_keys($fieldsMap), array_map(function($val){
                        return trim($val);
                    }, $row));
                } catch (\Exception $e) {
                    continue;
                }

                $row['created_at'] = strtotime($row['created_at']);
                $row['completed_at'] = strtotime($row['completed_at']);
                if('交易' == $row['bill_type']){
                    $row['bill_type'] = 'trade';
                }elseif('退款' == $row['bill_type']){
                    $row['bill_type'] = 'refund';
                }
                $row['total_amount'] = $row['total_amount'] * 100;
                $row['receipt_amount'] = $row['receipt_amount'] * 100;
                $row['service_amount'] = $row['service_amount'] * 100;
                list($func, $params) = $callback;
                $params['third_app_id'] = $trades['account'];
                call_user_func_array($func, [$row, $index, $params]);
            }
        }
    }
}
