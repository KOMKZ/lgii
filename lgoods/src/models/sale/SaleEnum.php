<?php
/**
 * Created by PhpStorm.
 * User: kitral
 * Date: 19-3-15
 * Time: 下午6:02
 */

namespace lgoods\models\sale;

class SaleEnum
{
    /**
     * 销售规则，商品对象
     */
    const SR_TYPE_GOODS = 1;

    /**
     * 销售规则，sku对象
     */
    CONST SR_TYPE_SKU = 2;

    /**
     * 销售规则，分类对象
     */
    CONST SR_TYPE_CATEGORY = 3;

    /**
     * 销售规则，订单对象
     */
    CONST SR_TYPE_ORDER = 4;

    /**
     * 销售规则模型，折扣
     */
    CONST SR_CACU_TYPE_DISCOUNT = 1;

    /**
     * 销售规则模型，满减
     */
    const SR_CACU_TYPE_FULL_SUB = 2;

    /**
     * 销售规则，有效
     */
    const SR_STATUS_VALID = 1;

    /**
     * 销售规则，无效
     */
    CONST SR_STATUS_INVALID = 2;
}