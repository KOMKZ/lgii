<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-11-25
 * Time: 下午3:34
 */
namespace lgoods\controllers;

use common\models\CartModel;
use lbase\Controller;
use lgoods\models\cart\CItemModel;
use Yii;
use yii\data\ActiveDataProvider;

class LcartItemController extends Controller{
    /**
     * @api post,/lcart-item,CartItem,加入购物车动作
     * - ci_sku_id required,integer,in_body,商品id
     * - ci_amount required,integer,in_body,购买商品的数量
     * - ci_belong_uid required,integer,in_body,添加用户id
     *
     * @return #global_res
     * - data object#cart_item,返回购物车条目对象
     *
     * <<<doc
     * 1. 注意重复添加同个商品只会数量增加，不会出现多条记录
     * >>>
     *
     */
    public function actionCreate(){
        $postData = Yii::$app->request->getBodyParams();
        $ciItem = CItemModel::findFull()->andWhere(['=', 'ci_sku_id', $postData['ci_sku_id']])->one();
        $ciModel = new CItemModel();
        if(!$ciItem){
            $ciItem = $ciModel->createItem($postData);
            if(!$ciItem){
                return $this->error(1, $ciModel->getErrors());
            }
        }else{
            CItemModel::adjustAmount($ciItem, (int)$postData['ci_amount']);
        }

        return $this->succ(CItemModel::formatOne(CItemModel::findFull()->
            where(['ci_id' => $ciItem['ci_id']])->asArray()->one()
        ));
    }
    /**
     * @api put,/lcart-item,CartItem,更新购物车条目
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
        $ciItem = CItemModel::findFull()->andWhere(['=', 'ci_id', $index])->one();
        if(!$ciItem){
            return $this->notfound();
        }
        $ciModel = new CItemModel();
        $postData = Yii::$app->request->getBodyParams();
        $ciItem = $ciModel->updateItem($ciItem, $postData);
        if(!$ciItem){
            return $this->error(1, $ciModel->getErrors());
        }
        return $this->succ(CItemModel::formatOne(CItemModel::findFull()->
        where(['ci_id' => $ciItem['ci_id']])->asArray()->one()
        ));
    }

    /**
     * @api delete,/lcart-item,CartItem,加入购物车动作
     * - ci_id required,integer,in_path,条目的id
     *
     * @return #global_res
     * - data integer,删除成功返回1
     *
     */
    public function actionDelete($index){
        $ciItem = CItemModel::findFull()->andWhere(['=', 'ci_id', $index])->one();
        if(!$ciItem){
            return $this->notfound();
        }
        $ciModel = new CItemModel();
        $postData = Yii::$app->request->getBodyParams();
        $ciModel->removeItem($ciItem);
        return $this->succ(1);
    }

    /**
     * @api get,/lcart-item,CartItem,查询购物车条目
     *
     * @return #global_res
     * - data object#cart_item_list,返回购物车条目列表
     */
    public function actionList(){
        $query = CItemModel::findFull()->asArray();
        $getData = Yii::$app->request->get();
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->succItems(CItemModel::formatList($provider->getModels(), $getData), $provider->totalCount);
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