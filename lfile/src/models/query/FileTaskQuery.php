<?php
namespace lfile\models\query;

use lfile\models\FileEnum;
use yii\base\Object;
use lfile\models\ar\FileTask;
use lfile\models\FileModel;

/**
 *
 */
class FileTaskQuery extends Object{
    public static function find(){
        return FileTask::find();
    }

    /**
     * 查询一个文件分片任务
     * @param  array $data 文件分片任务请求过来的数据
     * 1. 文件分片任务创建时使用该请求的数据来创建一个分片文件任务的id
     * 2. 查询该任务的时候也需要提供这些请求参数才能定位到该文件任务对象
     * @param  string $type 文件任务id生成方法
     * @see \lfile\models\FileModel::buildTaskUniqueString
     * @todo 这个方法应该返回query对象
     * @return [type]       [description]
     */
    public static function findOneCUByData($data, $type = 'hash_post'){
        return FileTaskQuery::find()->
                              where(['file_task_code' => FileModel::buildTaskUniqueString($type, $data), 'file_task_type' => FileEnum::TASK_CHUNK_UPLOAD])->
                              one();
    }
}
