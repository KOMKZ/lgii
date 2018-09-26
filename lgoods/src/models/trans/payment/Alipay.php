<?php
namespace lgoods\models\trans\payment;

use Yii;
use yii\base\Model;

/**
 *
 */
class Alipay extends Model
{
	CONST NAME = 'alipay';
	CONST MODE_URL = 'url';
	CONST MODE_APP = 'app';

	public $gatewayUrl;
	public $appId;
	public $rsaPrivateKeyFilePath;
	public $alipayrsaPublicKey;
	public $apiVersion = '1.0';
	public $signType = 'RSA2';
	public $postCharset = 'utf-8';
	public $format = 'json';
	public $notifyUrl = '';
	public $returnUrl = '';
	public $orderTimeOut = '30m';
	private $_aopClient = null;
	public function init(){
		$this->_aopClient = new \AopClient();
		$this->_aopClient->gatewayUrl = $this->gatewayUrl;
		$this->_aopClient->appId = $this->appId;
		$this->_aopClient->rsaPrivateKeyFilePath = $this->rsaPrivateKeyFilePath;
		$this->_aopClient->alipayrsaPublicKey = $this->alipayrsaPublicKey;
		$this->_aopClient->apiVersion = $this->apiVersion;
		$this->_aopClient->signType = $this->signType;
		$this->_aopClient->postCharset = $this->postCharset;
		$this->_aopClient->format = $this->format;
	}

	public function billDownload($filter = []){
		$baseDir = Yii::getAlias('@app/runtime/bill/ali');
		if(!is_dir($baseDir)){
			FileHelper::createDirectory($baseDir, 0777);
		}
		$logFile = Yii::getAlias('@app/runtime/logs/alibill.log');
		$request = new \AlipayDataDataserviceBillDownloadurlQueryRequest ();
		$request->setBizContent("{" .
			"\"bill_type\":\"" . $filter['type'] . "\"," .
			"\"bill_date\":\"" . $filter['date'] . "\"" .
			"  }");
		$result = $this->getAopClient()->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		if('Success' == $result->$responseNode->msg){
			$url = $result->$responseNode->bill_download_url;
			$billDir = $baseDir . '/' . $filter['date'];
			if(!is_dir($billDir)){
				mkdir($billDir, 0777);
			}
			$targetFile = $billDir . '/' . 'bill.zip';
			$bash = sprintf('curl "%s" -o %s -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36" -L --compressed 2>> /dev/null 1>> %s',
						   $url, $targetFile, $logFile);

			shell_exec($bash);
			return $targetFile;
		}else{
			$this->addError('', $result->$responseNode->sub_code);
			return false;
		}
	}
	public function getAopClient(){
		return $this->_aopClient;
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
		try {
			$result['notify_data_origin'] = $notifyData;
			$result['notify_data_parse'] = $notifyData;
			if(0 && !$this->_aopClient->rsaCheckV1($notifyData, $this->alipayrsaPublicKey, 'RSA2')){
				$result['error'] = Yii::t('app', '支付宝通知数据验证失败');
				$result['errno'] = PayModel::NOTIFY_INVALID;
			}
			$result['trans_number'] = $notifyData['out_trade_no'];
			$alipayOrder = $this->queryOrder(['trans_number' => $notifyData['out_trade_no']]);
			if(!$alipayOrder){
				list($code, $error) = $this->getOneError();
				$result['error'] = Yii::t('app', $error);
				$result['errno'] = PayModel::NOTIFY_ORDER_INVALID;
				return false;
			}
			$result['third_order'] = $alipayOrder;
			$result['trans_is_payed'] = $this->checkOrderIsPayed($alipayOrder);
			$result['code'] = 0;
			return $result;
		} catch (\Exception $e) {
			Yii::error($e);
			$result['error'] = $e->getMessage();
			$result['errno'] = PayModel::NOTIFY_EXCEPTION;
			return $result;
		}
	}
	public function saySucc($data = []){
		echo 'success';
		exit();
	}
	public function sayFail($data = []){
		echo "failure";
		exit();
	}
	public function formatReturn($masterData, $response){
		return [
			'master_data' => $masterData,
			'response' => $response,
		];
	}
	public function queryRefund($data){
		try {
			$request = new \AlipayTradeFastpayRefundQueryRequest();
			// out_request_no
			// 请求退款接口时，传入的退款请求号，如果在退款请求时未传入，则该值为创建交易时的外部交易号
			// https://docs.open.alipay.com/api_1/alipay.trade.fastpay.refund.query/
			$request->setBizContent("{" .
			"\"out_trade_no\":\"" . $data['trans_number'] . "\"," .
			"\"out_request_no\":\"" . $data['trans_number'] . "\"" .
			"}");
			$result = $this->_aopClient->execute($request);
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			if(!empty($resultCode) && $resultCode == 10000){
				return (array)$result->$responseNode;
			}{
				$this->addError('', Yii::t('app', sprintf('支付宝查询退款失败:%s,%s,%s,%s',
					$result->$responseNode->code,
					$result->$responseNode->msg,
					$result->$responseNode->sub_code,
					$result->$responseNode->sub_msg
				)));
				return false;
			}
		} catch (\Exception $e) {
			Yii::error($e);
			$this->addError('', $e->getMessage());
			return false;
		}
	}
	public function createRefund($data){
		// 支付宝的退款是同步的,失败和成功都在单个请求的响应可以知道,而微信则不是
		try {
			$request = new \AlipayTradeRefundRequest ();
			$request->setBizContent("{" .
			"\"out_trade_no\":\"" . $data['trans_number'] . "\"," .
			"\"refund_amount\":" . $data['trans_refund_fee']/100 . "," .
			"\"refund_reason\":\"" . $data['trans_refund_reasons'] . "\"" .
			"  }");
			$result = $this->_aopClient->execute($request);
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			if(!empty($resultCode) && $resultCode == 10000){
				// 注意,支付宝的订单的创建是需要一些触发动作,比如扫码之后才会有订单
				// 否则会一直报交易不存在记录
				return (array)$result->$responseNode;
			}{
				$this->addError('', Yii::t('app', sprintf('关闭支付宝订单失败:%s,%s,%s,%s',
					$result->$responseNode->code,
					$result->$responseNode->msg,
					$result->$responseNode->sub_code,
					$result->$responseNode->sub_msg
				)));
				return false;
			}
		} catch (\Exception $e) {
			Yii::error($e);
			$this->addError('', $e->getMessage());
			return false;
		}
	}

	public function checkOrderIsPayed($thirdOrder){
		if($thirdOrder
			&& in_array($thirdOrder['trade_status'], ['TRADE_SUCCESS']))
		{
			return true;
		}
		return false;
	}



	public function queryOrder($data){
		try {
			$request = new \AlipayTradeQueryRequest();
			$request->setBizContent("{" .
				"\"out_trade_no\":" . "\"" . $data['trans_number'] . "\"" .
			"}"
			);
			$result = $this->_aopClient->execute($request);
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			if(!empty($resultCode) && $resultCode == 10000){
				// 注意,支付宝的订单的创建是需要一些触发动作,比如扫码之后才会有订单
				// 否则会一直报交易不存在记录
				return (array)$result->$responseNode;
			}{
				$this->addError('', Yii::t('app', sprintf('查询支付宝订单失败:%s,%s,%s,%s',
					$result->$responseNode->code,
					$result->$responseNode->msg,
					$result->$responseNode->sub_code,
					$result->$responseNode->sub_msg
				)));
				return false;
			}

		} catch (\Exception $e) {
			Yii::error($e);
			$this->addError('', $e->getMessage());
			return false;
		}
	}

	public function closeOrder($data){
		try {
			$request = new \AlipayTradeCloseRequest();
			$request->setBizContent("{" .
				"\"out_trade_no\":" . "\"" . $data['trans_number'] . "\"" .
			"}"
			);
			$result = $this->_aopClient->execute($request);

			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			if(!empty($resultCode) && $resultCode == 10000){
				// 注意,支付宝的订单的创建是需要一些触发动作,比如扫码之后才会有订单
				// 否则会一直报交易不存在记录
				return (array)$result->$responseNode;
			}else{
				$this->addError('', Yii::t('app', sprintf('关闭支付宝订单失败:%s,%s,%s,%s',
					$result->$responseNode->code,
					$result->$responseNode->msg,
					$result->$responseNode->sub_code,
					$result->$responseNode->sub_msg
				)));
				return false;
			}
		} catch (\Exception $e) {
			Yii::error($e);
			$this->addError('', $e->getMessage());
			return false;
		}



	}

	/**
	 *
	 * @param  [type] $data [description]
	 * - trans_return_url:
	 * - trans_invalid_at:
	 * - trans_start_at:
	 * - trans_number:
	 * - trans_title:
	 * - trans_total_fee:
	 * - trans_detail:
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public function createOrder($data, $type){
		try {
			switch ($type) {
				case static::MODE_URL:
					$result = $this->createOrderForUrl($data);
					return $this->formatReturn($result, $result);
				case static::MODE_APP:
					$result = $this->createOrderForApp($data);
					return $this->formatReturn($result, $result);
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
    public function getThirdTransId($payOrder){
        $thirdData = json_decode($payOrder->pt_third_data);
        return $thirdData->pay_succ_notification->trade_no;
    }
	public function createOrderForUrl($data){
		$preOrder = new \AlipayTradePagePayRequest();
		if(!empty($data['trans_return_url'])){
			$preOrder->setReturnUrl($data['pay_return_url']);
		}else{
			$preOrder->setReturnUrl($this->returnUrl);
		}
		if(!empty($data['trans_invalid_at'])){
			$timeoutExpress = round(($data['trans_invalid_at'] - $data['trans_start_at'])/60) . 'm';
		}else{
			$timeoutExpress = $this->orderTimeOut;
		}
		$preOrder->setNotifyUrl($this->notifyUrl);
		$preOrder->setBizContent("{" .
			"\"timeout_express\":" . "\"" . $timeoutExpress . "\"," .
			"\"product_code\": \"FAST_INSTANT_TRADE_PAY\"," .
			"\"out_trade_no\":" . "\"" . $data['trans_number'] . "\"," .
			"\"subject\":" . "\"" . $data['trans_title'] . "\"," .
			"\"total_amount\":" . "\"" . ($data['trans_total_fee']/100) . "\"," .
			"\"body\":" . "\"" . $data['trans_detail'] . "\"" .
		"}"
		);
		$payUrl = $this->_aopClient->pageExecute($preOrder, 'get');
		return $payUrl;
	}

	public function createOrderForApp($data){
		$preOrder = new \AlipayTradeAppPayRequest();
		if(!empty($data['trans_return_url'])){
			$preOrder->setReturnUrl($data['pay_return_url']);
		}else{
			$preOrder->setReturnUrl($this->returnUrl);
		}
		if(!empty($data['trans_invalid_at'])){
			$timeoutExpress = ($data['trans_invalid_at'] - $data['trans_start_at'])/60 . 'm';
		}else{
			$timeoutExpress = $this->orderTimeOut;
		}
		$preOrder->setNotifyUrl($this->notifyUrl);
		$preOrder->setBizContent("{" .
			"\"timeout_express\":" . "\"" . $timeoutExpress . "\"," .
			"\"product_code\": \"FAST_INSTANT_TRADE_PAY\"," .
			"\"out_trade_no\":" . "\"" . $data['trans_number'] . "\"," .
			"\"subject\":" . "\"" . $data['trans_title'] . "\"," .
			"\"total_amount\":" . "\"" . ($data['trans_total_fee']/100) . "\"," .
			"\"body\":" . "\"" . $data['trans_detail'] . "\"" .
		"}"
		);
		$paySignString = $this->_aopClient->sdkExecute($preOrder);
		return $paySignString;
	}





}
