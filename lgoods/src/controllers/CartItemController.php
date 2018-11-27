<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-11-25
 * Time: 下午3:34
 */
namespace lgoods\controllers;

use lbase\Controller;
use lgoods\models\cart\CItemModel;
use Yii;

class CartItemController extends Controller{
    /**
     * @api post,/cart-item,CartItem,加入购物车动作
     * - ci_sku_id required,integer,in_body,商品id
     * - ci_amount required,integer,in_body,购买商品的数量
     * - ci_belong_uid required,integer,in_body,添加用户id
     *
     * @return #global_res
     * - data object#cart_item,返回购物车条目对象
     *
     */
    public function actionCreate(){
        $t = $this->beginTransaction();
        $postData = Yii::$app->request->getBodyParams();
        $ciModel = new CItemModel();
        $result = $ciModel->createItem($postData);
        if(!$result){
            return $this->error(1, $ciModel->getErrors());
        }
        return $this->succ(CItemModel::formatOne(CItemModel::findFull()->
        where(['ci_id' => $result['ci_id']])->asArray()->one()
        ));
    }
    /**
     * @api put,/cart-item,CartItem,更新购物车条目
     * - ci_id required,integer,in_path,条目的id
     * - ci_sku_id optional,integer,in_body,商品id
     * - ci_amount optional,integer,in_body,购买商品的数量
     * - ci_belong_uid required,integer,in_body,添加用户id
     *
     * @return #global_res
     * - data object#cart_item,返回购物车条目对象
     *
     */
    public function actionUpdate($index){

    }

    /**
     * @api delete,/cart-item,CartItem,加入购物车动作
     * - ci_id required,integer,in_path,条目的id
     *
     * @return #global_res
     * - data integer,删除成功返回1
     *
     */
    public function actionDelete($index){

    }

    /**
     * @api get,/cart-item,CartItem,查询购物车条目
     *
     * @return #global_res
     * - data object#cart_item_list,返回购物车条目列表
     */
    public function actionList(){

    }

}
/**
 * @def #cart_item
 * - ci_id integer,主键
 * - ci_g_id integer,商品id
 * - ci_sku_id integer,商品sku的id
 * - ci_amount integer,商品购买的数量
 * - ci_total_price integer,商品的总价格
 * - ci_belong_uid integer,条目所属用户id
 * - ci_created_at integer,条目创建时间
 * - ci_updated_at integer,条目更新时间
 * - ci_status integer,条目的状态
 *
 * @def #cart_item_list
 * - total_count integer,总数量
 * - items array#cart_item,条目列表
 */