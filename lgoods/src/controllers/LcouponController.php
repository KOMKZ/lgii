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

class LcouponController extends Controller{


    /**
     * @api get,/lcoupon,Coupon,创建coupon
     *
     * @return #global_res
     * - data object#coupon_item_list,返回coupon列表对象
     *
     */
    public function actionList(){
        $query = CouponModel::find()->asArray();
        $getData = Yii::$app->request->get();
        if(!empty($getData['coup_object_id'])){
            $query->andWhere(['=', 'coup_object_id', $getData['coup_object_id']]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->succItems(CouponModel::formatList($provider->getModels(), $getData), $provider->totalCount);
    }

    /**
     * @api get,/lcoupon/{index},Coupon,获取coupon详情信息
     * - coup_id required,integer,in_body,coupon的id
     *
     * @return #global_res
     * - data object#coupon_item,返回coupon图详情信息
     *
     */
    public function actionView($index){
        $coupon = CouponModel::find()->andWhere(['=', 'coup_id', $index])->asArray()->one();
        if(!$coupon){
            return $this->notfound();
        }
        return $this->succ(CouponModel::formatOne($coupon));
    }

    /**
     * @api delete,/lcoupon/{index},Coupon,删除coupon
     * - coup_id required,integer,in_body,coupon的id
     *
     * @return #global_res
     * - data integer,删除成功返回1
     *
     */
    public function actionDelete($index){
        $coupon = CouponModel::find()->andWhere(['=', 'coup_id', $index])->one();
        if(!$coupon){
            return $this->notfound();
        }
        $bModel = new CouponModel();
        $bModel->updateCoupon($coupon, ['coup_status' => Coupon::STATUS_DELETE]);
        return $this->succ(1);
    }

    /**
     * @api post,/lcoupon,Coupon,创建coupon
     * - coup_name required,integer,in_body,优惠券名称
     * - coup_caculate_type required,integer,in_body,优惠券计算模型
     * - coup_caculate_params required,integer,in_body,优惠券计算模型参数
     * - coup_object_id required,integer,in_body,优惠券作用对象id
     * - coup_object_type required,integer,in_body,优惠券作用对象类型
     * - coup_limit_params required,integer,in_body,优惠券限制使用参数
     * - coup_start_at required,integer,in_body,开始时间
     * - coup_end_at required,integer,in_body,结束时间
     * - coup_status required,integer,in_body,状态
     * - coup_usage_intro required,integer,in_body,使用说明
     *
     * @return #global_res
     * - data object#coupon_item,返回coupon图详情信息
     *
     */
    public function actionCreate(){
        $postData = Yii::$app->request->getBodyParams();
        $bModel = new CouponModel();
        $coupon = $bModel->createCoupon($postData);
        if(!$coupon){
            return $this->error(1, $bModel->getErrors());
        }
        return $this->succ(CouponModel::formatOne($coupon->toArray()));
    }

    /**
     * @api put,/lcoupon/{index},Coupon,修改coupon
     * - coup_id required,integer,in_path,coupon的id
     * - coup_name optional,integer,in_body,优惠券名称
     * - coup_usage_intro optional,integer,in_body,使用说明
     *
     * @return #global_res
     * - data object#coupon_item,返回coupon图详情信息
     *
     */
    public function actionUpdate($index){
        $coupon = CouponModel::find()->andWhere(['=', 'coup_id', $index])->one();
        if(!$coupon){
            return $this->notfound();
        }
        $postData = Yii::$app->request->getBodyParams();
        $bModel = new CouponModel();
        $coupon = $bModel->updateCoupon($coupon, $postData);
        if(!$coupon){
            return $this->error(1, $bModel->getErrors());
        }
        return $this->succ(CouponModel::formatOne($coupon->toArray()));
    }
}
/**
 *
 * @def #coupon_item
 * - coup_id integer,主键
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
 * @def #coupon_item_list
 * - total_count integer,数量
 * - items array#coupon_item,coupon列表
 *
 *
 *
 *
 */