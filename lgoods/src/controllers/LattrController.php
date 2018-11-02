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




class LattrController extends Controller{



    public function actionCreate(){
        $postData = Yii::$app->request->getBodyParams();
        if($this->checkDataIsArray($postData)){
            return $this->creates($postData);
        }
        $model = new AttrModel();
        $attr = $model->createAttr($postData);
        if(!$attr){
            return $this->error(1, $model->getErrors());
        }
        return $this->succ($attr->toArray());
    }

    public function creates($data){
        $t = $this->beginTransaction();
        try{
            $model = new AttrModel();
            $count = $model->createAttrs($data);
            if(false === $count){
                return $this->error(1, $model->getErrors());
            }
            $t->commit();
            return $this->succ($count);
        }catch(\Exception $e){
            $t->rollBack();
            throw $e;
        }
    }

    public function actionUpdate($index){
        $attr = AttrModel::findAttr()->andWhere(['a_id' => $index])->one();
        if(!$attr){
            return $this->notfound();
        }
        $postData = Yii::$app->request->getBodyParams();
        $model = new AttrModel();
        $attr = $model->updateAttr($attr, $postData);
        if(!$attr){
            return $this->error(1, $model->getErrors());
        }
        return $this->succ($attr->toArray());
    }


}