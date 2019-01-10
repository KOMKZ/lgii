<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 19-1-10
 * Time: 上午10:34
 */
namespace lsite\models\action;

interface ActionTargetInterface{
    public function getLogId();
    public function getLogParams($name);
}