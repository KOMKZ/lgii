<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-2
 * Time: 下午3:45
 */
namespace lgoods\models\attr;

use Yii;
use yii\base\Model;

class AttrModel extends Model{

    public function createAttr($data){
        $attr = new Attr();
        if(!$attr->load($data, '') || !$attr->validate()){
            $this->addErrors($attr->getErrors());
            return false;
        }
        $attr->insert(false);
        return $attr;
    }

    public function createAttrs($data){
        $ids = [];
        foreach($data as $num => $oneData){
            $attr = new Attr();
            if(!$attr->load($oneData, '') || !$attr->validate()){
                $this->addError('',$num . ":" . implode(',', $attr->getFirstErrors()));
                return false;
            }
            $attr->insert(false);
            $ids[] = $attr->a_id;
        }
        return $ids;
    }


    public function updateAttr($attr, $data){
        if(!$attr->load($data, '') || !$attr->validate()){
            $this->addErrors($attr->getErrors());
            return false;
        }
        $attr->update(false);
        return $attr;
    }

    public static function findAttr(){
        return Attr::find();
    }

    public static function findOption(){
        return Option::find();
    }

    public function createCollect($data){
        $attr = new AttrCollect();
        if(!$attr->load($data, '') || !$attr->validate()){
            $this->addErrors($attr->getErrors());
            return false;
        }
        $attr->insert(false);
        return $attr;
    }

    public function updateCollect($collect, $data){
        if(!$collect->load($data, '') || !$collect->validate()){
            $this->addErrors($collect->getErrors());
            return false;
        }
        $collect->update(false);
        return $collect;
    }

    public static function findCollect(){
        return AttrCollect::find();
    }

    public static function findFullCollect(){
        $query = AttrCollect::find()
                    ->with("ac_map")
                        ;
        return $query;
    }

    public function createAttrCollectAssign($collect, $aids){
        $data = [];
        foreach($aids as $aid){
            $data[] = [$collect->ac_id, $aid];
        }
        return Yii::$app->db->createCommand()->batchInsert(ACMap::tableName(), [
            'ac_id',
            'a_id'
        ], $data)->execute();

    }

    public function createObjectCollectAssign($data){
        $OCMap = new OCMap();
        if(!$OCMap->load($data, '') || !$OCMap->validate()){
            $this->addErrors($OCMap->getErrors());
            return false;
        }
        $OCMap->insert(false);
        return $OCMap;

    }

}