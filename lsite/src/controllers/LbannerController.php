<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 下午1:58
 */
namespace lsite\controllers;

use lsite\models\banner\BannerEnum;
use Yii;
use lbase\Controller;
use lsite\models\banner\Banner;
use lsite\models\banner\BannerModel;
use yii\data\ActiveDataProvider;

class LbannerController extends Controller{


    /**
     * @api get,/lbanner,Banner,创建banner图
     * - b_img_app optional,integer,in_query,banner图应用id
     * - b_img_module optional,integer,in_query,banner图模块id
     *
     * @return #global_res
     * - data object#banner_item_list,返回banner列表对象
     *
     */
    public function actionList(){
        $query = BannerModel::find()->asArray();
        $getData = Yii::$app->request->get();
        if(!empty($getData['b_img_app'])){
            $query->andWhere(['=', 'b_img_app', $getData['g_img_app']]);
        }
        if(!empty($getData['b_img_module'])){
            $query->andWhere(['=', 'b_img_module', $getData['b_img_module']]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->succItems(BannerModel::formatList($provider->getModels(), $getData), $provider->totalCount);
    }

    /**
     * @api get,/lbanner/{index},Banner,获取banner图详情信息
     * - b_img_id required,string,in_body,banner图id
     *
     * @return #global_res
     * - data object#banner_item,返回banner图详情信息
     *
     */
    public function actionView($index){
        $banner = BannerModel::find()->andWhere(['=', 'b_id', $index])->one();
        if(!$banner){
            return $this->notfound();
        }
        return $this->succ(BannerModel::formatOne($banner->toArray()));
    }

    /**
     * @api delete,/lbanner/{index},Banner,删除banner图
     * - b_img_id required,string,in_body,banner图id
     *
     * @return #global_res
     * - data integer,删除成功返回1
     *
     */
    public function actionDelete($index){
        $banner = BannerModel::find()->andWhere(['=', 'b_id', $index])->one();
        if(!$banner){
            return $this->notfound();
        }
        $bModel = new BannerModel();
        $bModel->updateBanner($banner, ['b_status' => BannerEnum::STATUS_DELETE]);
        return $this->succ(1);
    }

    /**
     * @api post,/lbanner,Banner,创建banner图
     * - b_img_id required,string,in_body,banner图id
     * - b_img_app required,integer,in_body,banner图应用id
     * - b_img_module required,integer,in_body,banner图模块id
     * - b_reffer_link required,string,in_body,参考链接
     * - b_reffer_label required,string,in_body,参考链接label
     *
     * @return #global_res
     * - data object#banner_item,返回banner图详情信息
     *
     */
    public function actionCreate(){
        $postData = Yii::$app->request->getBodyParams();
        $bModel = new BannerModel();
        $banner = $bModel->createBanner($postData);
        if(!$banner){
            return $this->error(1, $bModel->getErrors());
        }
        return $this->succ(BannerModel::formatOne($banner->toArray()));
    }

    /**
     * @api put,/lbanner/{index},Banner,修改banner图
     * - b_id required,integer,in_path,benner图id
     * - b_img_id optional,string,in_body,banner图id
     * - b_img_app optional,integer,in_body,banner图应用id
     * - b_img_module optional,integer,in_body,banner图模块id
     * - b_reffer_link optional,string,in_body,参考链接
     * - b_reffer_label optional,string,in_body,参考链接label
     *
     * @return #global_res
     * - data object#banner_item,返回banner图详情信息
     *
     */
    public function actionUpdate($index){
        $banner = BannerModel::find()->andWhere(['=', 'b_id', $index])->one();
        if(!$banner){
            return $this->notfound();
        }
        $postData = Yii::$app->request->getBodyParams();
        $bModel = new BannerModel();
        $banner = $bModel->updateBanner($banner, $postData);
        if(!$banner){
            return $this->error(1, $bModel->getErrors());
        }
        return $this->succ(BannerModel::formatOne($banner->toArray()));
    }
}
/**
 *
 * @def #banner_item
 * - b_id integer,主键
 * - b_img_id integer,图片id
 * - b_img_app integer,应用id
 * - b_img_module integer,模块id
 * - b_reffer_link string,参考链接
 * - b_reffer_label string,参考链接label
 * - b_img_created_at integer,创建时间
 * - b_img_updated_at integer,更新时间
 * - b_img_url string,图片url
 *
 * @def #banner_item_list
 * - total_count integer,数量
 * - items array#banner_item,banner图列表
 *
 *
 *
 *
 */