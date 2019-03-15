<?php
/**
 * Created by PhpStorm.
 * User: kitral
 * Date: 19-3-15
 * Time: 下午5:51
 */

namespace lgoods\models\goods;

class GoodsEnum
{
    /**
     * 商品事件，商品创建成功
     */
    CONST EVENT_GOODS_CREATE = 'goods_create';

    /**
     * 商品sku索引，有效
     */
    CONST GOODS_INDEX_STATUS_VALID = 1;

    /**
     * 商品sku索引，无效
     */
    CONST GOODS_INDEX_STATUS_INVALID = 2;
}