<?php
namespace lgoods\models\trans\payment;

use Yii;
use WxPayConfig;
use WxPayUnifiedOrder;
use WxPayOrderQuery;
use WxPayApi;
use WxPayRefund;
use WxPayRefundQuery;
use WxPayCloseOrder;
use WxPayResults;
use yii\base\Model;
use yii\base\InvalidArgumentException;
/**
 *
 */
class Wxpay extends Model
{
    const NAME = 'wxpay';
    const MODE_NATIVE = 'NATIVE';
    CONST MODE_APP = 'APP';

    public $appid;

    public $mchid;

    public $key;

    public $appsecret;

    public $sslcert_path;

    public $sslkey_path;

    public $notifyUrl;
    public function init(){
        parent::init();
        WxPayConfig::$APPID = $this->appid;
        WxPayConfig::$MCHID = $this->mchid;
        WxPayConfig::$KEY = $this->key;
        WxPayConfig::$APPSECRET = $this->appsecret;
        WxPayConfig::$SSLCERT_PATH = $this->sslcert_path;
        WxPayConfig::$SSLKEY_PATH = $this->sslkey_path;
    }
    public function formatReturn($masterData, $response){
        return [
            'master_data' => $masterData,
            'response' => $response,
        ];
    }

    public function transfer($data){

    }

    /**
     * 下载对账单
     * @param  [type] $billDate [description]
     * @param  [type] $billType [description]
     * @return [type]           [description]
     */
    public function billDownload($filter){
        $baseDir = Yii::getAlias('@app/runtime/bill/wx');
        if(!is_dir($baseDir)){
            mkdir($baseDir, 0777);
        }

        @list($year, $month, $day) = explode('-', $filter['date']);
        $currentDate = '';
        $request = new \WxPayDownloadBill();
        if($day){
            $billDir = $baseDir . '/' . $currentDate;
            if(!is_dir($billDir)){
                mkdir($billDir, 0777);
            }
            $targetFile = $billDir . '/' . 'bill.csv';

            $currentDate = preg_replace('/\-/', '', $filter['date']);
            $request = new \WxPayDownloadBill();
            $request->SetBill_date($currentDate);
            $request->SetBill_type($filter['type']);
            return WxPayApi::downloadBill($request);
        }
        $endDate = sprintf("%d%02d31", $year, $month);
        $day = 1;
        $currentDate=sprintf("%d%02d%02d", $year, $month, $day);
        $billDir = $baseDir . '/' . sprintf("%d%02d", $year, $month);
        if(!is_dir($billDir)){
            mkdir($billDir, 0777);
        }
        $targetFile = $billDir . '/' . 'bill.csv';
        file_put_contents($targetFile, '');
        while($currentDate != $endDate){
            $request->SetBill_date($currentDate);
            $request->SetBill_type($filter['type']);
            $result = WxPayApi::downloadBill($request);
            if(preg_match_all('/(.+)/', $result, $matches)){
                array_pop($matches[0]);
                array_pop($matches[0]);
                array_shift($matches[0]);
                file_put_contents($targetFile, implode("\n", $matches[0]), FILE_APPEND);
            }
            $day++;
            if($day > 31){
                $day = 1;
                $month++;
            }
            if($month > 12){
                $month = 1;
                $year++;
            }
            $currentDate=sprintf("%d%02d%02d", $year, $month, $day);
        }
        return $targetFile;
    }

    public function sayFail($data = []){
        $notify = new \WxPayNotify();
        $notify->SetReturn_code("FAIL");
        $notify->SetReturn_msg('');
        return $notify->ReplyNotify(false);
    }

    public function saySucc($data){
        $notify = new \WxPayNotify();
        $notify->SetReturn_code("SUCCESS");
        $notify->SetReturn_msg("OK");
        return $notify->ReplyNotify(false);
    }

    public function handleNotify($notifyData, $params = []){
        $result = [
            'code' => 1,
            'error' => '',
            'errno' => '',
            'third_order' => null,
            'trans_number' => null,
            'trans_is_payed' => false,
            'notify_data_origin' => null,
            'notify_data_parse' => null,
            'type' => static::NAME
        ];
        $xml = empty($notifyData) ? $GLOBALS['HTTP_RAW_POST_DATA'] : $notifyData;

        try {
            $result['notify_data_origin'] = $xml;
            $data = WxPayResults::Init($xml);
            $result['notify_data_parse'] = $data;

            if(empty($data['out_trade_no'])){
                $result['errno'] = TransModel::NOTIFY_INVALID;
                $result['error'] = Yii::t('app', "微信通知数据out_trade_no不存在");
                return $result;
            }
            $thirdOrder = $this->queryOrder(['trans_number' => $data['out_trade_no']]);
            if(!$thirdOrder){
                list($code, $error) = $this->getOneError();
                $result['error'] = $error;
                $result['errno'] = TransModel::NOTIFY_ORDER_INVALID;
                return $result;
            }
            $result['code'] = 0;
            $result['third_order'] = $thirdOrder;
            $result['trans_number'] = $data['out_trade_no'];
            $result['trans_is_payed'] = $this->checkOrderIsPayed($thirdOrder);
            return $result;
        } catch (\WxPayException $e){
            $result['error'] = $e->errorMessage();
            $result['errno'] = TransModel::NOTIFY_EXCEPTION;
            return $result;
        }

        return call_user_func($callback, $result);
    }

    public function getThirdTransId($payOrder, $isRf = false){
        $thirdData = json_decode($payOrder->pt_third_data);

        if(!$isRf){
            $data = \WxPayResults::Init($thirdData->pay_succ_notification);
            return $data['transaction_id'];
        }else{
            return $thirdData->refund_id;
        }

    }

    public function queryRefund($data){
        try {
            $input = new WxPayRefundQuery();
            $input->SetOut_trade_no($data["trans_number"]);
            $result = WxPayApi::refundQuery($input);
            if(!$this->checkResultIsValid($result)){
                return false;
            }
            return $result;
        } catch (\Exception $e) {
            Yii::error($e);
            $this->addError('', $e->getMessage());
            return false;
        }
    }

    public function checkOrderIsRefunded($thirdRefund){
        if($thirdRefund
            //todo
           && in_array($thirdRefund['refund_status_0'], ['SUCCESS'])
        ){
            return true;
        }
        return false;
    }

    public function closeOrder($data){
        try {
            $input = new WxPayCloseOrder();
            $input->SetOut_trade_no($data["trans_number"]);
            $result = WxPayApi::closeOrder($input);
            if(!$this->checkResultIsValid($result)){
                return false;
            }
            // todo check result
            return $result;
        } catch (\Exception $e) {
            Yii::error($e);
            $this->addError('', $e->getMessage());
            return false;
        }
    }

    public function createRefund($data){
        try {
            ini_set('date.timezone','Asia/Shanghai');
        	$input = new WxPayRefund();
        	$input->SetOut_trade_no($data["trans_number"]);
        	$input->SetTotal_fee($data["trans_total_fee"]);
        	$input->SetRefund_fee($data["trans_refund_fee"]);
            $input->SetOut_refund_no($data["trans_refund_number"]);
            $input->SetOp_user_id(WxPayConfig::$MCHID);
        	$result = WxPayApi::refund($input);
            if(!$this->checkResultIsValid($result)){
                return false;
            }
            return $result;
        } catch (\Exception $e) {
            // crul 58 证书错误,路径或者内容无效
            Yii::error($e);
            $this->addError('', $e->getMessage());
            return false;
        }
    }
    public function queryOrder($data){
        try {
            $input = new WxPayOrderQuery();
            $input->SetOut_trade_no($data['trans_number']);
            $result = WxPayApi::orderQuery($input);
            if(!$this->checkResultIsValid($result)){
                return false;
            }
            return $result;
        } catch (\Exception $e) {
            Yii::error($e);
            $this->addError('', $e->getMessage());
            return false;
        }
    }

    public function checkOrderIsPayed($thirdOrder){
        if($thirdOrder
            && in_array($thirdOrder['trade_state'], ['SUCCESS']))
        {
            return true;
        }
        return false;
    }


    public function createOrder($data, $type){
        try {
            switch ($type) {
                case static::MODE_NATIVE:
                    $result = $this->createNativeOrder($data);
                    if(!$this->checkResultIsValid($result)){
                        return false;
                    }
                    return $this->formatReturn($result['code_url'], $result);
                case static::MODE_APP:
                    $result = $this->createAppOrder($data);
                    if(!$this->checkResultIsValid($result)){
                        return false;
                    }
                    $data = $this->signDataForApp($result);
                    return $this->formatReturn($data, $result);
                default:
                    throw new InvalidArgumentException(Yii::t('app', "{$type}不支持的交易类型"));
                    break;
            }
        } catch (\Exception $e) {
            Yii::error($e);
            $this->addError('', $e->getMessage());
            return false;
        }
    }

    protected function checkResultIsValid($result){
        if('FAIL' == $result['return_code']){
            // https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1
            // 错误代码列表
            $this->addError('', Yii::t("app", "微信响应错误:{$result['return_msg']}"));
            return false;
        }
        if('FAIL' == $result['result_code']){
            // error https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4
            $this->addError('', Yii::t("app", "微信响应错误:{$result['err_code']}.{$result['err_code_des']}"));
            return false;
        }
        return true;
    }

    public function createAppOrder($data){
        try {
            $input = new WxPayUnifiedOrder();
            $input->SetBody($data['trans_title']);
            $input->SetOut_trade_no($data['trans_number']);
            $input->SetTotal_fee($data['trans_total_fee']);
            $input->SetTime_start(date("YmdHis", $data['trans_start_at']));
            $input->SetTime_expire(date("YmdHis", $data['trans_invalid_at']));
            $input->SetNotify_url($this->notifyUrl);
            $input->SetTrade_type("APP");
            $input->SetProduct_id($data['trans_product_id']);
            $result = WxPayApi::unifiedOrder($input);
            return $result;
        } catch (\Exception $e) {
            Yii::error($e);
            $this->addError('', $e->getMessage());
            return false;
        }


    }

    protected function signDataForApp($data){
        $params = [];
        $params['appid'] = $data['appid'];
        $params['timestamp'] = '' . time();
        $params['noncestr'] = md5(uniqid(mt_rand(), true));
        $params['package'] = 'Sign=WXPay';
        $params['prepayid'] = $data['prepay_id'];
        $params['partnerid'] = $data['mch_id'];
        $params['sign'] =  static::getDataSignature($params);
        return $params;
    }

    public function createNativeOrder($data){
        $input = new WxPayUnifiedOrder();
        $input->SetBody($data['trans_title']);
        $input->SetOut_trade_no($data['trans_number']);
        $input->SetTotal_fee($data['trans_total_fee']);
        $input->SetTime_start(date("YmdHis", $data['trans_start_at']));
        $input->SetTime_expire(date("YmdHis", $data['trans_invalid_at']));
        $input->SetNotify_url($this->notifyUrl);
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($data['trans_product_id']);
        $input->SetAttach($data['trans_detail']);
        $result = WxPayApi::unifiedOrder($input);
        return $result;
    }


    protected static function getDataSignature($values){
        ksort($values);
        $string = self::toUrlParams($values);
        $string = $string . "&key=".WxPayConfig::$KEY;
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }
    protected static function toUrlParams($values)
    {
        $buff = "";
        foreach ($values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}
