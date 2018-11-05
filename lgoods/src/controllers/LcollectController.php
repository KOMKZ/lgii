<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:13
 */
namespace lgoods\controllers;


use lgoods\models\attr\AttrModel;
use Yii;
use lbase\Controller;
use yii\data\ActiveDataProvider;


class LcollectController extends Controller{

    public function actionView($index){
        $collect = AttrModel::findFullCollect()
                                ->andWhere(['ac_id' => $index])
                                ->asArray()
                                ->one();
        if(!$collect){
            return $this->notfound();
        }
        return $this->succ($collect);
    }

    public function actionList(){
        $query = AttrModel::findFullCollect();
        $provider = new ActiveDataProvider([
            'query' => $query->asArray(),
        ]);
        return $this->succItems($provider->getModels(), $provider->totalCount);
    }

    public function actionCreate(){
        $t = $this->beginTransaction();
        try{
            // 创建属性集合
            $postData = Yii::$app->request->getBodyParams();
            $model = new AttrModel();
            $collect = $model->createCollect($postData);
            if(!$collect){
                return $this->error(1, $model->getErrors());
            }
            // 创建属性
            $aids = $model->createAttrs($postData['attrs']);
            if(false === $aids){
                return $this->error(1, $model->getErrors());
            }

            if(0 == count($aids)){
                return $this->errorParams();
            }
            if(count($aids) != count($postData['attrs'])){
                return $this->errorParams();
            }
            // 注入属性
            $count = $model->createAttrCollectAssign($collect, $aids);
            if(false === $count){
                return $this->error(1, $model->getErrors());
            }
            $t->commit();
            return $this->succ($collect->toArray());
        }catch(\Exception $e){
            $t->rollBack();
            throw $e;
        }

    }

    public function actionUpdate($index){
        $collect = AttrModel::findCollect()->andWhere(['ac_id' => $index])->one();
        if(!$collect){
            return $this->notfound();
        }
        $postData = Yii::$app->request->getBodyParams();
        $model = new AttrModel();
        $collect = $model->updateCollect($collect, $postData);
        if(!$collect){
            return $this->error(1, $model->getErrors());
        }
        return $this->succ($collect->toArray());
    }


}