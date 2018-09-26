<?php
namespace lgoods\models\trans;

use Yii;
use yii\base\Model;
use lgoods\models\trans\Trans;
use lgoods\models\trans\PayTrace;
use lgoods\models\trans\payment\Wxpay;
use lgoods\models\trans\payment\Alipay;
use lgoods\models\trans\AfterPayedEvent;

/**
 *
 */
class TransModel extends Model
{
    public static $map = [
        Alipay::NAME => [
            PayTrace::TYPE_DATA => Alipay::MODE_APP,
            PayTrace::TYPE_URL => Alipay::MODE_URL
        ],
        Wxpay::NAME => [
            PayTrace::TYPE_DATA => Wxpay::MODE_APP,
            PayTrace::TYPE_URL => Wxpay::MODE_NATIVE
        ]
    ];

    CONST NOTIFY_INVALID = 'notify_invalid';
    CONST NOTIFY_ORDER_INVALID = 'notify_order_invalid';
    CONST NOTIFY_EXCEPTION = 'notify_exception';

    public static function handleReceivePayedEvent($event){
        $payOrder = $event->sender;
        $trans = static::findTrans()
            ->andWhere(['=', 'trs_num', $payOrder->pt_belong_trans_number])
            ->one();

        if(Trans::TPS_PAID == $trans->trs_pay_status){
            // 该交易已经支付 记录一下日志即可 todo
            // Yii::info(["通知得到的数据但是交易已经在平台处于支付状态", $payOrder->toArray()], "trans_payed_repeated")
            return ;
        }
        // 修改交易数据
        $trans->trs_pay_type = $payOrder->pt_pay_type;
        $trans->trs_pay_status = Trans::TPS_PAID;
        $trans->trs_pay_at = time();
        $payment = static::getPayment($payOrder->pt_pay_type);
        $trans->trs_pay_num = $payment->getThirdTransId($payOrder);
        if(false === $trans->update(false)){
            throw new \Exception(Yii::t('app', "更改交易失败"));
        }

        // 查找交易所属用户，分发给其他模块
        $event = new AfterPayedEvent();
        $event->belongUser = null;
        $event->payOrder = $payOrder;
        static::triggerTransPayed($trans, $event);
    }

    public static function triggerTransPayed($trans, $event = null){
        $trans->trigger(Trans::EVENT_AFTER_PAYED, $event);
    }

    public function createTransFromOrder($order, $params = []){
        $trans = new Trans();
        $trans->trs_type = Trans::TRADE_ORDER;
        $trans->trs_target_id = $order->od_id;
        $trans->trs_target_num = $order->od_num;
        $trans->trs_fee = $order->od_price;
        $trans->trs_pay_status = Trans::TPS_NOT_PAY;
        $trans->trs_pay_at = time();
        $trans->trs_pay_type = '';
        $trans->trs_pay_num = '';
        $trans->trs_content = $params['trs_content'];
        $trans->trs_num = static::buildTradeNumber();
        $trans->trs_timeout = $params['trs_timeout'];
        $trans->trs_title = sprintf("购买-%s", $order['od_title']);
        $trans->insert(false);
        return $trans;
    }

    public static function findPayTrace(){
        return PayTrace::find();
    }

    public static function findTrans(){
        return Trans::find();
    }

    protected static function buildTradeNumber(){
        list($time, $millsecond) = explode('.', microtime(true));
        $string = sprintf("TR%s%04d", date("HYisdm", $time), $millsecond);
        return $string;
    }

    public static function getPayment($type){
        switch ($type) {
            case Wxpay::NAME:
                return Yii::$app->wxpay;
            case Alipay::NAME:
                return Yii::$app->alipay;
            default:
                throw new InvalidArgumentException(Yii::t('app', "{$type}不支持的支付类型"));
                break;
        }
    }

    public static function triggerPayed($payOrder){
        $payOrder->trigger(PayTrace::EVENT_AFTER_PAYED);
    }

    public function updatePayOrderPayed($payOrder, $data){
        if(!empty($data['notification'])){
            $payOrder->third_data = ['pay_succ_notification' => $data['notification']];
        }
        $payOrder->pt_pay_status = PayTrace::PAY_STATUS_PAYED;
        $payOrder->pt_status = PayTrace::STATUS_PAYED;
        if(false === $payOrder->update(false)){
            $this->addError(Errno::DB_UPDATE_FAIL, Yii::t('app', "修改支付单支失败"));
            return false;
        }
        return $payOrder;
    }


    public function createPayOrderFromTrans($trans, $data){
        $t = Yii::$app->db->beginTransaction();
        try {
            $data['pt_status'] = PayTrace::STATUS_INIT;
            $data['pt_pay_status'] = PayTrace::PAY_STATUS_NOPAY;
            $payOrder = new PayTrace();
            $payOrder->pt_pay_type = $data['pt_pay_type'];
            $payOrder->pt_pre_order = '';
            $payOrder->pt_belong_trans_number = $trans['trs_num'];
            $payOrder->pt_pre_order_type = $data['pt_pre_order_type'];
            $payOrder->pt_pay_status = PayTrace::PAY_STATUS_NOPAY;
            $payOrder->pt_status = PayTrace::STATUS_INIT;
            $payOrder->pt_third_data = '';
            if(empty($data['pt_timeout'])){
                $payOrder->pt_timeout = $trans->trs_timeout;
            }else{
                $payOrder->pt_timeout = $data['pt_timeout'];
            }

            if(!$this->validate()){
                throw new \Exception(implode(',', $payOrder->getFirstErrors()));
            }
            $payOrder->insert(false);


            $payment = static::getPayment($payOrder->pt_pay_type);
            $payData = [
                'trans_invalid_at' => $payOrder->pt_timeout + time(),
                'trans_start_at' => time(),
                'trans_number' => $payOrder->pt_belong_trans_number,
                'trans_title' => $trans->trs_title,
                'trans_total_fee' => $trans->trs_fee,
                'trans_detail' => $trans->trs_content,
                'trans_product_id' => $trans->trs_target_num,
            ];

            $thirdPreOrder = $payment->createOrder($payData, static::$map[$payOrder->pt_pay_type][$payOrder->pt_pre_order_type]);

            if(!$thirdPreOrder){
                $this->addErrors($payment->getErrors());
                return false;
            }

            $payOrder->pt_pre_order = $thirdPreOrder['master_data'];
            $payOrder->third_data = [
                'pre_response' => $thirdPreOrder['response']
            ];
            if(false === $payOrder->update(false)){
                $this->addError(Errno::DB_UPDATE_FAIL, Yii::t('app', "修改支付单失败"));
                return false;
            }
            $t->commit();
            return $payOrder;
        } catch (\Exception $e) {
            $t->rollback();
            Yii::error($e);
            $this->addError(Errno::EXCEPTION, Yii::t('app', "创建支付单异常"));
            return false;
        }
    }

}