<?php
namespace lgoods\models\trans;

use yii\base\Event;

/**
 *
 */
class AfterPayedEvent extends Event
{

    public $belongUser = null;

    public $payOrder = null;

    public $refund = null;

    public $order = null;

}
