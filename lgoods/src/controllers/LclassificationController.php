<?php
namespace lgoods\controllers;

use Yii;
use lbase\Controller;
use lgoods\models\category\ClassificationModel;
use lgoods\models\category\GoodsClassification;
/**
 *
 */
class lclassificationController extends Controller
{
    /**
     * @api get,/lclassification,Category,查询分类信息
     *
     * @return #global_res
     * - data object#category_tree_list,分类目录树
     *
     */
    public function actionList(){
        $result = ClassificationModel::findClsAsTree();
        return $this->succItems($result, count($result));
    }

    /**
     * @api put,/lclassification/{id},Category,更新分类信息
     *
     * @return #global_res
     * - data object#category_item,返回修改的信息
     *
     */
    public function actionUpdate($index){
        try {
            $postData = Yii::$app->request->getBodyParams();
            $cls = ClassificationModel::find()->andWhere(['=', 'g_cls_id', $index])->one();
            if(!$cls){
                return $this->error(404, Yii::t('app', "指定的分类不存在"));
            }
            $clsModel = new ClassificationModel();
            $result = $clsModel->updateGoodsClassification($cls, $postData);
            if(!$result){
                return $this->error(null, $clsModel->getErrors());
            }
            return $this->succ($result->toArray());
        }catch(\Exception $e){
            throw $e;
        }

    }
    /**
     * @api delete,/lclassification/{id},Category,删除分类信息
     *
     * @return #global_res
     * - data object#category_item,返回修改的信息
     *
     */
    public function actionDelete($index){
        try {
            $postData = Yii::$app->request->getBodyParams();
            $cls = ClassificationModel::find()->andWhere(['=', 'g_cls_id', $index])->one();
            if(!$cls){
                return $this->succ([]);
            }
            $clsModel = new ClassificationModel();
            $result = $clsModel->removeClassification($cls);
            if(!$result){
                return $this->error(null, $clsModel->getErrors());
            }
            return $this->succ([]);
        }catch(\Exception $e){
            throw $e;
        }

    }

    /**
     *
     * @api post,/lclassification,Category,创建一个分类信息
     * - g_cls_name required,string,in_body,分类名称
     * - g_cls_show_name optional,string,in_body,分类展示名称，如果为空的时候则默认填充g_cls_name
     * - g_cls_pid optional,integer,in_body,分类府级分类id
     *
     * @return #global_res
     * - data object#category_item,返回刚创建的分类信息
     *
     */
    public function actionCreate(){
        try {
            $postData = Yii::$app->request->getBodyParams();
            $clsModel = new ClassificationModel();
            $result = $clsModel->createGoodsClassification($postData);
            if(!$result){
                return $this->error(null, $clsModel->getErrors());
            }
            return $this->succ($result);
        }catch(\Exception $e){
            throw $e;
        }


    }
}


/**
 * @def #category_item
 * - g_cls_name string,分类名称
 * - g_cls_pid string,分类父级id
 * - g_cls_show_name string,分类展示名称
 * - g_cls_id string,分类id
 * - g_cls_created_at string,分类创建时间
 *
 * @def #category_tree_list
 * - total_count integer,总数量
 * - items array#category_tree,分类列表
 *
 * @def #category_tree
 * - g_cls_name string,分类名称
 * - g_cls_pid string,分类父级id
 * - g_cls_show_name string,分类展示名称
 * - g_cls_id string,分类id
 * - g_cls_img_id string,分类图片id
 * - g_cls_img_url string,分类图片url
 * - nodes array#category_tree,下级分类
 *
 */