<?php
/**
 * Created by PhpStorm.
 * User: kitral
 * Date: 19-3-15
 * Time: 下午6:08
 */

namespace lgoods\models\trans;

class TransEnum
{
    CONST PT_STATUS_INIT = 'init';
    CONST PT_STATUS_CANCEL = 'cancel';
    CONST PT_STATUS_PAYED = 'payed';
    CONST PT_STATUS_ERROR = 'error';

    CONST PT_PAY_STATUS_PAYED = 'payed';
    CONST PT_PAY_STATUS_NOPAY = 'nopay';

    CONST PT_TYPE_DATA = 'data';
    CONST PT_TYPE_URL = 'url';

    CONST EVENT_AFTER_PAYED = "pay_order_payed";
    CONST EVENT_AFTER_RFED = 'pay_refund';

    CONST EVENT_AFTER_UPDATE = 'afterUpdate';


    CONST TRADE_ORDER = 1;

    CONST TRADE_TRANS = 2;

    CONST TRADE_REFUND = 3;



    CONST TPS_NOT_PAY = 0;


    CONST TPS_PAID = 1;
    const EVENT_TRS_AFTER_PAYED = 'trans_paid';

    CONST EVENT_TRS_AFTER_RFED = 'trans_rfed';
}