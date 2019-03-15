<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 下午2:04
 */
namespace lsite\models\banner;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Banner extends ActiveRecord{


    public static function tableName(){
        return "{{%banner}}";
    }
    public function rules(){
        return [
            [
                [
                    'b_img_id',
                    'b_img_app',
                    'b_img_module',
                    'b_reffer_link',
                    'b_reffer_label',
                ],
                'safe'
            ],
            ['b_status', 'default', 'value' => BannerEnum::STATUS_VALID]
        ];
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'b_created_at',
                'updatedAtAttribute' => 'b_updated_at'
            ]
        ];
    }
}