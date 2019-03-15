<?php
/**
 * Created by PhpStorm.
 * User: kitral
 * Date: 19-3-15
 * Time: 下午5:59
 */

namespace lgoods\models\refund;

class RfEnum
{
    /**
     * 退款状态，已经提交
     */
    CONST STATUS_SUBMIT = 'submit';

    /**
     * 退款状态，已经退款
     */
    CONST STATUS_HAD_REFUND = 'hadrf';
}