<?php
namespace lgoods\models\goods;

use yii\base\Event;

class GoodsEvent extends Event{
    public $goodsData = [];

    public $object = null;
}