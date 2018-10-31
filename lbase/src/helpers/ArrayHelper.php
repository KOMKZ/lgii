<?php
namespace lbase\helpers;

use yii\helpers\BaseArrayHelper;

/**
 *
 */
class ArrayHelper extends BaseArrayHelper
{
    /**
     * 转换数组拼接成为字符串
     * 规则如下：
     * 先按键名排序数组
     * 0,'0', false => '0'
     * true => '1',
     * null => '',
     * Array => 递归进行得到字符串再拼接
     * @param  Array  $array [description]
     * @return [type]        [description]
     */
    public static function concatAsString(Array $array){
        ksort($array);
        $buff = "";
        foreach ($array as $k => $v)
        {
            if(is_numeric($v)){
                $v = ''.$v;
            }elseif(is_bool($v)){
                $v = ''.(int)$v;
            }elseif(is_array($v)){
                $v = static::concatAsString($v);
            }
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}
