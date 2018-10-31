<?php
namespace lfile\models\ar;

use Yii;
use lbase\helpers\FileHelper;
use lfile\models\FileModel;
use lfile\models\drivers\Disk;
use lbase\staticdata\ConstMap;
use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;
/**
 *
 */
class File extends ActiveRecord
{
    public $file_source_path = '';

    public static function tableName(){
        return "{{%file}}";
    }


    public function getFile_query_id(){
        return FileModel::buildFileQueryId($this);
    }

    public function getFile_url(){
        return FileModel::buildFileUrl($this);
    }

    /**
     * 获取文件保存地址
     * 本方法主要用于上传时确定文件保存地址用
     * @return [type] [description]
     */
    public function getFileSavePath(){
        return FileModel::buildFileSavePath($this);
    }

    /**
     * 获取本地文件的完整路径
     * @return [type] [description]
     */
    public function getFileDiskFullSavePath(){
        // $mediumInfo = json_decode($this->file_medium_info);
        return Yii::$app->filedisk->base . '/' . $this->getFileSavePath();
    }


    public function fields(){
        $attrs = array_merge(parent::attributes(), [
            'file_query_id',
            'file_url'
        ]);
        $extra = ['file_source_path', 'file_medium_info'];
        foreach($extra as $attr){
            ArrayHelper::removeValue($attrs, $attr);
        }
        return $attrs;
    }

    public function scenarios(){
        return [
            'default' => [
                'file_is_private',
                'file_is_tmp',
                'file_save_name',
                'file_save_type',
                'file_valid_time',
                'file_source_path',
                'file_category',
                'file_prefix'
            ],
            'chunkupload' => [
                'file_is_private',
                'file_is_tmp',
                'file_save_name',
                'file_save_type',
                'file_valid_time',
                'file_category',
                'file_prefix'
            ]
        ];
    }

    public function rules(){
        return [
            ['file_is_private', 'default', 'value' => 0],
            ['file_is_private', 'integer'],
            ['file_is_private', 'in', 'range' => ConstMap::getConst('file_is_private', true)],

            ['file_is_tmp', 'default', 'value' => 1],
            ['file_is_tmp', 'integer'],
            ['file_is_tmp', 'in', 'range' => ConstMap::getConst('file_is_tmp', true)],


            ['file_save_name', 'string'],
            ['file_save_name', 'filter', 'filter' => function($value){
                return FileHelper::buildFileSafeName($value);
            }],

            ['file_save_type', 'default', 'value' => Disk::NAME],
            ['file_save_type', 'in', 'range' => ConstMap::getConst('file_save_type', true)],

            ['file_valid_time', 'default', 'value' => 0],
            ['file_valid_time', 'integer'],

            ['file_source_path', 'string'],
            ['file_source_path', function($attr){
                if(!file_exists($this->$attr)){
                    $this->addError('source_path', Yii::t('app', "{$this->$attr} 文件不存在"));
                }
            }],

            ['file_category', 'required'],
            ['file_category', 'filter', 'filter' => function($value){
                return ltrim(trim($value, '/'), '/');
            }]
        ];
    }




}
