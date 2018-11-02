<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-2
 * Time: 下午2:00
 */
namespace lgoods\models\attr;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class AttrCollect extends ActiveRecord{
    public static function tableName(){
        return "{{%attr_collect}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'ac_created_at',
                'updatedAtAttribute' => 'ac_updated_at'
            ]
        ];
    }

    public function getAc_map(){
        $aTable = Attr::tableName();
        $acMap = ACMap::tableName();
        return $this->hasMany(ACMap::class, [
            'ac_id' => 'ac_id'
        ])
            ->select([
                "{$aTable}.a_name",
                "{$aTable}.a_id",
                "{$acMap}.ac_id"
            ])
            ->leftJoin($aTable, "{$aTable}.a_id = {$acMap}.a_id");
    }

    public function rules(){
        return [
            ["ac_name", 'required']
        ];
    }
}