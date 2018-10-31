<?php
namespace lfile\models\ar;

use Yii;
use common\models\staticdata\ConstMap;
use yii\db\ActiveRecord;
/**
 *
 */
class FileTask extends ActiveRecord
{
    CONST TASK_CHUNK_UPLOAD = 'chunk';

    CONST STATUS_INIT = 'init';
    CONST STATUS_INVALID = 'invalid';

    public static function tableName(){
        return "{{%file_task}}";
    }






    public function rules(){
        return [
            ['file_task_code', 'required'],
            ['file_task_code', 'string'],

            ['file_task_type', 'in', 'range' => ConstMap::getConst('file_task_type')],

            ['file_task_start_at', 'integer'],
            ['file_task_start_at', 'default', 'value' => time()],

            ['file_task_invalid_at', 'integer'],
            ['file_task_invalid_at', 'default', 'value' => 0],

            ['file_task_completed_at', 'integer'],
            ['file_task_completed_at', 'default', 'value' => 0],

            ['file_task_status', 'in', 'range' => ConstMap::getConst('file_task_status')],
            ['file_task_status', 'default', 'value' => self::STATUS_INIT],

            ['file_task_data', 'default', 'value' => '']

        ];
    }




}
