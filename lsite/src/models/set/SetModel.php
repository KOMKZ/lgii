<?php
namespace lsite\models\set;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\base\InvalidValueException;
/**
 *
 */
class SetModel extends Model
{
    public static function get($name, $allowNull = false, $default = null){
        $params = static::getSettings();
        $value = ArrayHelper::getValue($params, $name, $default);
        if(!$allowNull && null === $value){
            throw new InvalidValueException(Yii::t('app', "设置{$name}不存在"));
        }else{
            return $value;
        }
    }
    public static function getSettings(){
        return Yii::$app->params;
    }
}
