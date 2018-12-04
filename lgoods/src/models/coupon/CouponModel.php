<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-30
 * Time: 下午2:03
 */
namespace lgoods\models\coupon;

use yii\base\Model;

class CouponModel extends Model{
    public static function find(){
        return Coupon::find();
    }
    public static function formatList($list, $params = []){
        return $list;
    }
    public static function formatUcList($list, $params){
        return $list;
    }
    public static function formatUcOne($item, $params = []){
        return $item;
    }
    public static function formatOne($item, $params = []){
        return $item;
    }
    public function updateCoupon(Coupon $coupon, $data){
        $coupon->scenario = 'update';
        if(!$coupon->load($data, '') || !$coupon->validate()){
            $this->addErrors($coupon->getErrors());
            return false;
        }
        $coupon->update(false);
        return $coupon;
    }
    public static function findUserCoupon(){
        return UserCoupon::find();
    }
    public static function findUserCouponFull(){
        $query = static::findUserCoupon();
        $query->from(['ucou' => UserCoupon::tableName()]);
        $query->leftJoin(["coup" => Coupon::tableName()], "coup.coup_id = ucou.coup_id");
        $query->select([
            "ucou.*",
            "coup.*"
        ]);
        return $query;
    }
    public function createUserCoupon($data){
        $uc = new UserCoupon();
        if(!$uc->load($data, '') || !$uc->validate()){
            $this->addErrors($uc->getErrors());
            return false;
        }
        $uc->ucou_status = UserCoupon::STATUS_NOT_USE;
        $uc->ucou_created_at = time();
        $uc->insert(false);
        return $uc;
    }
    public function createCoupon($data){
        $coupon = new Coupon();
        if(!$coupon->load($data, '') || !$coupon->validate()){
            $this->addErrors($coupon->getErrors());
            return false;
        }
        $coupon->coup_status = Coupon::STATUS_VALID;
        $coupon->insert(false);
        return $coupon;
    }




}