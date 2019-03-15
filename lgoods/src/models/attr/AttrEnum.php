<?php
/**
 * Created by PhpStorm.
 * User: kitral
 * Date: 19-3-15
 * Time: 下午5:34
 */

namespace lgoods\models\attr;

class AttrEnum
{
    /**
     * 属性值内容类型，富文本
     */
    CONST A_TYPE_FULL_TEXT = 1;

    /**
     * 属性值内容类型，sku属性
     */
    CONST A_TYPE_SKU = 2;

    /**
     * 属性值内容类型，常规属性
     */
    CONST A_TYPE_NORMAL = 3;

    /**
     * 选项值所属对象，商品
     */
    CONST OPT_OBJECT_TYPE_GOODS = 1;
}