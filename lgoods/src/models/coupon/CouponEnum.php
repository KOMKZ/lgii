<?php
/**
 * Created by PhpStorm.
 * User: kitral
 * Date: 19-3-15
 * Time: 下午5:45
 */

namespace lgoods\models\coupon;

class CouponEnum
{
    /**
     * 删除状态
     */
    const STATUS_DELETE = 2;

    /**
     * 有效状态
     */
    const STATUS_VALID = 1;


    /**
     * 规则类型，商品销售规则
     */
    const SR_TYPE_GOODS = 1;

    /**
     * 规则类型，sku销售规则
     */
    CONST SR_TYPE_SKU = 2;

    /**
     * 规则类型，商品分类规则
     */
    CONST SR_TYPE_CATEGORY = 3;

    /**
     * 规则类型，订单规则
     */
    CONST SR_TYPE_ORDER = 4;

    /**
     * 用户优惠券状态，未使用
     */
    CONST USER_COUPON_STATUS_NOT_USE = 1;

    /**
     * 用户优惠券状态，已经使用
     */
    CONST USER_COUPON_STATUS_USED = 2;

    /**
     * 用户优惠券状态, 无效状态
     */
    CONST USER_COUPON_STATUS_INVALID = 3;
}