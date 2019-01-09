<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 下午1:56
 */
namespace lsite\models\action;

use lfile\models\FileModel;
use Yii;
use yii\base\Model;

class ActionLogModel extends Model{
    public static function find(){
        return Banner::find()->andWhere(['=', 'b_status', Banner::STATUS_VALID]);
    }
    public static function formatOne($one, $params = []){
        $one['b_img_url'] = Yii::$app->file->buildFileUrlStatic(FileModel::parseQueryId($one['b_img_id']));
        return $one;
    }
    public static function formatList($list, $params = []){
        foreach($list as &$one){
            $one = static::formatOne($one, $params);
        }
        return $one;
    }
    public function createBanner($data){
        $banner = new Banner();
        if(!$banner->load($data, '') || !$banner->validate()){
            $this->addErrors($banner->getErrors());
            return false;
        }
        $banner->insert(false);
        return $banner;
    }
    public function updateBanner($banner, $data){
        if(!$banner->load($data, '') || !$banner->validate()){
            $this->addErrors($banner->getErrors());
            return false;
        }
        $banner->update(false);
        return $banner;
    }

}