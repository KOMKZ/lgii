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

    const APP_WEB = 1;
    CONST APP_ANDROID = 2;
    CONST APP_IOS = 3;

    CONST MODULE_WEB_HOME = 1;

    CONST STATUS_VALID = 1;
    CONST STATUS_DELETE = 2;

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
            ['b_status', 'default', 'value' => Banner::STATUS_VALID]
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