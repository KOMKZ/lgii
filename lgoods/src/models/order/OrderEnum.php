<?php
/**
 * Created by PhpStorm.
 * User: kitral
 * Date: 19-3-15
 * Time: 下午5:56
 */

namespace lgoods\models\order;

class OrderEnum
{
    CONST PS_NOT_PAY = 0;

    CONST PS_PAID = 1;

    CONST EVENT_AFTER_PAID = 'order_paid';
}
