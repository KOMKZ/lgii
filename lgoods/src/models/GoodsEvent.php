<?php
namespace lgoods\models;

use yii\base\Event;

class GoodsEvent extends Event{
    public $goodsData = [];

    public $object = null;
}