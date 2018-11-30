<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 下午1:58
 */
namespace lgoods\controllers;

use lgoods\models\coupon\Coupon;
use Yii;
use lbase\Controller;
use lgoods\models\coupon\CouponModel;
use yii\data\ActiveDataProvider;

class LuserCouponController extends Controller{


    /**
     * @api get,/luser-coupon,Coupon,创建coupon
     *
     * @return #global_res
     * - data object#user_coupon_item_list,返回user_coupon列表对象
     *
     */
    public function actionList(){
        $query = CouponModel::findUserCouponFull()->asArray();
        $getData = Yii::$app->request->get();
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->succItems(CouponModel::formatUcList($provider->getModels(), $getData), $provider->totalCount);
    }



    /**
     * @api post,/luser-coupon,Coupon,创建coupon
     * - coup_id required,integer,in_body,优惠券的id
     * - coup_uid required,integer,in_body,优惠券所属用户id
     *
     * @return #global_res
     * - data object#user_coupon_item,返回coupon图详情信息
     *
     */
    public function actionCreate(){
        $postData = Yii::$app->request->getBodyParams();
        $bModel = new CouponModel();
        $userCoupon = $bModel->createUserCoupon($postData);
        if(!$userCoupon){
            return $this->error(1, $bModel->getErrors());
        }
        return $this->succ(CouponModel::formatUcOne($userCoupon->toArray()));
    }

}
/**
 *
 * @def #user_coupon_item
 * - coup_id integer,优惠券id
 * - ucou_id integer,主键id
 * - ucou_uid integer,所属用户id
 * - ucou_status integer,哟你
 * - coup_name string,优惠券名称
 * - coup_caculate_type integer,优惠券计算模型
 * - coup_caculate_params integer,优惠券计算模型参数
 * - coup_object_id integer,优惠券作用对象id
 * - coup_object_type integer,优惠券作用对象类型
 * - coup_object_type_des string,优惠券作用对象类型说明
 * - coup_limit_params string,优惠券限制使用参数
 * - coup_limit_params_des string,优惠券限制使用参数说明
 * - coup_created_at integer,创建时间
 * - coup_updated_at integer,更新时间
 * - coup_start_at integer,开始时间
 * - coup_end_at integer,结束时间
 * - coup_status integer,状态
 * - coup_usage_intro string,使用说明
 * - coup_reffer_url string,web去使用url
 * - coup_reffer_link string,app去使用link
 *
 * @def #user_coupon_item_list
 * - total_count integer,数量
 * - items array#user_coupon_item,coupon列表
 *
 *
 *
 *
 */