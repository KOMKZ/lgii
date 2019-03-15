<?php
namespace lfile\models;

use Yii;
use yii\base\Model;
use lfile\models\ar\File;
use lfile\models\ar\FileTask;
use lfile\models\drivers\Disk;
use lfile\models\drivers\Oss;
use lbase\staticdata\ConstMap;
use yii\helpers\FileHelper;
use lbase\helpers\ArrayHelper;
use yii\base\InvalidParamException;
use yii\base\InvalidArgumentException;
use lfile\models\query\FileTaskQuery;

/**
 *
 */
class FileModel extends Model
{
    public static $globalUploadParams = [];

    public function deleteFile(File $file){
        // 先删数据
        // 再删文件
        $file->delete();
        $saveDriver = static::getSaveMedium($file->file_save_type);
        $saveDriver->deleteFile($file);
        return true;
    }

    /**
     * 从一个文件路径来上传文件到文件存储中
     * 内部流程如下：
     * 1. 创建文件对象
     * 2. 上传到对应的存储中
     * 3. 保存到数据库
     * @param  array $fileData 文件数据
     * @see \lfile\models\FileModel::createFile
     * @return \lfile\models\ar\File
     */
    public function createFileBySource($fileData){
        $file = $this->createFile($fileData);
        if(!$file){
            return false;
        }
        $file = $this->saveFile($file);
        if(!$file){
            return false;
        }
        $file = $this->saveFileInDb($file);
        if(!$file){
            return false;
        }
        return $file;
    }

    /**
     * 通过复制保存文件在存储媒介中
     * 该方法暂时没有实现不同媒介之间的复制
     * @param  File   $targetFile 目标文件
     * @param  File   $originFile 源文件
     * @return File   返回目标文件
     */
    public function saveFileByCopy(File $targetFile, File $originFile){
        $saveMedium = $this->getSaveMedium($originFile->file_save_type);
        if($targetFile->file_save_type == $originFile->file_save_type){
            $file = $saveMedium->saveByCopy($targetFile, $originFile);
        }else{
            throw new \Exception("还没有实现");
        }
        $file->file_medium_info = json_encode($saveMedium->buildMediumInfo());
        $file->file_created_time = time();
        return $file;
    }

    /**
     * 创建一个文件对象
     * @param  array $data 文件对象的基础数据
     * - file_is_private: integer, 文件访问属性
     *   定义文件的访问属性，默认值是0
     *   1:私有文件
     *   0:共有文件
     * - file_is_tmp: integer, 文件是否是临时文件
     *   定义文件的时效型，默认值是0
     *   1:是临时文件，将被定时任务定时清理
     *   0:永久文件
     * - file_valid_time: integer, 临时文件的有效时间
     *   仅当is_tmp为1的时候有效，如定义3600,说明文件在服务器的有效时间是3600秒
     * - file_save_name: string, 文件的下载名称
     *   该属性将被用于设置文件下载时的响应头
     * - file_source_path: string,required 文件的源路径
     *   注意文件路径必须携带完整的文件名称，包括文件后缀
     *   当参数isCopy传为true的时候，说明可以不需要源路径
     * - file_category: string,required 文件分类信息
     *   文件分类如
     *   /user/image/ 将会被转成 user/image
     *   最中被计算为md5
     * @return \lfile\models\ar\File
     */
    public function createFile($data, $isCopy = false){
        if(!empty($data['file_category'])){
            $data = array_merge($data, $this->getFromCategory($data['file_category']));
        }
        if(!$file = $this->validateFileData($data)){
            return false;
        }
        $file->file_ext = pathinfo($file->file_save_name, PATHINFO_EXTENSION);
        $file->file_md5_value = $isCopy ? $data['file_md5_value'] : md5_file($file->file_source_path);
        $file->file_real_name = self::buildFileRealName($file);
        $file->file_valid_time = 1 == $file->file_is_tmp ? $file->file_valid_time : 0;
        return $file;
    }

    public function getFromCategory($type){
        if(empty(static::$globalUploadParams)){
            static::$globalUploadParams = include(Yii::getAlias("@app/config/nm-file-params.php"));
            $localFile = Yii::getAlias("@app/config/nm-file-params-local.php");
            if(file_exists($localFile)){
                static::$globalUploadParams = array_merge(
                    static::$globalUploadParams,
                    include($localFile)
                );
            }
        }
        $params = ArrayHelper::getValue(static::$globalUploadParams, $type, []);
        return $params;
    }

    /**
     * 从文件分片中来上传新建一个文件
     * @param  array $fileInfo   文件的分片信息和文件信息
     * - chunk: 文件分片的序号
     * - chunks: 文件分片的数量
     * 其他支持的字段 @see createFile 方法
     * 创建文件分片流将会先验证文件信息是否合法，在所有分片都到达之后合并分片文件，然后从本地保存到指定的存储媒介中。
     * @param  array $fileStream 文件分片流，目前不支持
     * @return File $file 所有文件分片上传成功，返回合并后的分片对象，使用createFile创建。
     */
    public function createFilePart($fileInfo, $fileStream = null){
        if(!isset($fileInfo['chunk']) || !isset($fileInfo['chunks']) || !is_numeric($fileInfo['chunk']) || !is_numeric($fileInfo['chunks'])){
            $this->addError("", Yii::t('app', '分片上传参数不完整'));
            return false;
        }
        $fileTask = FileTaskQuery::findOneCUByData($fileInfo);
        if(!$fileTask || !static::checkFileTaskIsExpired($fileTask)){
            $this->addError('', Yii::t('app', "分片任务不存在/文件任务已经失效"));
            return false;
        }
        $chunkIndex = (int)$fileInfo['chunk'];
        $chunkTotal = (int)$fileInfo['chunks'];
        if(0 == $chunkIndex){
            // 先验证文件信息
            if(!$this->validateFileData($fileInfo, 'chunkupload')){
                return false;
            }
            // 初始化分片目录
            $chunkDir = static::buildFileChunkDir($fileTask);
            $fileInfoFile = $chunkDir . '/file.txt';
            file_put_contents($fileInfoFile, serialize($fileInfo));
        }
        $chunkDir = static::getFileChunkDir($fileTask);
        if(empty($_FILES) || empty($_FILES['file']) || $_FILES["file"]["error"]){
            $this->addError('', Yii::t('app','没有文件数据/文件上传错误:'. $_FILES['file']['error']));
            return false;
        }
        $chunkFile = $chunkDir . '/file.' . $chunkIndex;
        $new = 0;
        if(!file_exists($chunkFile) || $_FILES['file']['size'] != filesize($chunkFile)){
            move_uploaded_file($_FILES['file']['tmp_name'], $chunkFile);
            $new = 1;
        }
        if($chunkIndex != $chunkTotal - 1){
            return ['chunk' => $chunkIndex, 'new' => $new];
        }
        $finalFilePath = $chunkDir . '/file.final';
        if(file_exists($finalFilePath)){
            unlink($finalFilePath);
        }
        $finalFile = @fopen($chunkDir . '/file.final', "ab");
        if(!$finalFile){
            $this->addError('', Yii::t('app', '打开文件流失败'));
            return false;
        }
        $i = 0;
        while($i < $chunkTotal){
            $in = @fopen($chunkDir . '/file.' . $i, "rb");
            if(!$in){
                return $this->error('', Yii::t('app', '打开分片文件流失败'));
                return false;
            }
            while ($buff = fread($in, 4096))fwrite($finalFile, $buff);
            @fclose($in);
            $i++;
        }
        @fclose($finalFile);
        $fileInfo = unserialize(file_get_contents($chunkDir . '/file.txt'));
        $fileData = array_merge([
            'file_source_path' => $finalFilePath
        ], $fileInfo);
        $file = $this->createFile($fileData);
        if(!$file){
            return false;
        }
        $file = $this->saveFile($file);
        if(!$file){
            return false;
        }
        $file = $this->saveFileInDb($file);
        if(!$file){
            return false;
        }
        FileHelper::removeDirectory($chunkDir);
        return $file;
    }

    /**
     * 验证文件数据的合法性
     * 具体的验证规则@lfile\models\ar\File;
     * @param  array $data     文件数据
     * @param  string $scenario 验证场景
     * @return File $file 验证成功将创建对应的文件对象返回
     */
    public function validateFileData($data, $scenario = 'default'){
        $file = new File();
        $file->scenario = $scenario;
        if(!$file->load($data, '') || !$file->validate()){
            $this->addErrors($file->getErrors());
            return false;
        }
        return $file;
    }

    /**
     * 创建一个文件任务
     * @param  array $data 文件任务数据
     * - file_task_code:string,文件任务值，
     * - file_task_type:string,任务类型,请使用常量代替
     * - file_task_start_at:integer,开始时间
     * - file_task_invalid_at:integer,失效时间
     * - file_task_status:string,初始状态，请使用常量代替
     * - file_task_data:string,相关数据
     * @return FileTask $fileTask 返回文件任务对象
     */
    public function createFileTask($data){
        $fileTask = new FileTask();
        if(!$fileTask->load($data, '') || !$fileTask->validate()){
            $this->addErrors($fileTask->getErrors());
            return false;
        }
        $fileTask->insert(false);
        return $fileTask;
    }
    /**
    * 创建一个文件分片任务
    * 创建一个文件分片上传需要有分片任务的支持，分片任务的业务号由分片上传的业务参数决定，
    * 每次提交过来的参数经过一定的计算得到任务值，查找任务值存在且有效则允许上传的接续。
    * @see self::buildTaskUniqueString 方法了解详细任务值的产生
     * @param  array   $fileInfo      文件信息
     * @param  integer $validDuration 分片任务有效时间
     * @return FileTask                 文件任务
     */
    public function createFileChunkedUploadTask($fileInfo = [], $validDuration = 86400){
        $fileTaskData = [
            'file_task_code' => static::buildTaskUniqueString('hash_post', $fileInfo),
            'file_task_invalid_at' => time() + $validDuration,
            'file_task_type' => FileEnum::TASK_CHUNK_UPLOAD,
            'file_task_data' => json_encode($fileInfo),
        ];
        return $this->createFileTask($fileTaskData);
    }

    /**
     * 构建文件任务值
     * @param  string $type 任务值产生方式
     * 可以是以下值，
     * hash_post:针对文件分片任务
     * @param  array  $data 相关数据
     * @return string       文件任务值
     */
    public static function buildTaskUniqueString($type = 'hash_post', $data = []){
        switch ($type) {
            case 'hash_post':
                $hashAttrs = array_merge(['access_token', 'timestamp'], (new File())->attributes());
                foreach($data as $key => $value){
                    if(!in_array($key, $hashAttrs)) unset($data[$key]);
                }
                return empty($data) ? null : md5(ArrayHelper::concatAsString($data));
            case 'simple_uniqid':
                return md5(microtime(true) . uniqid());
            default:
                throw new InvalidArgumentException(Yii::t("{$type}不支持的参数值"));
                break;
        }
    }

    /**
     * 删除无效的文件任务
     * @return integer 返回删除的文件数量
     */
    public static function clearInvalidTask(){
        return FileTask::deleteAll("
            file_task_invalid_at <= :current_time
            or
            file_task_status = :status
        ", [
            ':current_time' => time(),
            ':status' => FileEnum::TASK_STATUS_INVALID
        ]);
    }

    /**
     * 保存文件到存储媒介
     * @param  File   $file 文件对象
     * @return File       返回文件对象
     */
    public function saveFile(File $file){
        $saveMedium = $this->getSaveMedium($file->file_save_type);
        $file = $saveMedium->save($file);
        if(!$file){
            throw new \Exception('保存文件失败');
        }
        $file->file_medium_info = json_encode($saveMedium->buildMediumInfo());
        $file->file_created_time = time();
        return $file;
    }

    /**
     * 保存文件数据到数据数据库
     * @param  \lfile\models\ar\File   $file 统一文件对象
     * @return \lfile\models\ar\File
     */
    public function saveFileInDb(File $file){
        $file->insert(false);
        return $file;
    }

    /**
     * 检查文件api查询id是否符合规则
     * api文件查询id满足以下规则属于合法：
     * {$file_save_type}:{file_category/}{file_name}[.file_ext]
     * 如：oss:abc.jpg
     * @param  string $queryId 文件查询id
     * @return [type]          [description]
     */
    public static function checkIsValidQueryid($queryId){
        $typeList = implode('|', ConstMap::getConst('file_save_type', true));
        return preg_match("/^({$typeList}):{1}(.+)/", $queryId);
    }

    public static function checkSignature($signature, $data = []){
        return false;
    }

    /**
     * 检查文件上传任务对象是否已经失效
     * @param  FileTask $fileTask 文件上传任务对象
     * @return bool
     * true说明有效，false无效
     */
    public static function checkFileTaskIsExpired(FileTask $fileTask){
        return true;
        return time() < $fileTask->file_task_invalid_at;
    }

    /**
     * 解析文件api查询id得到文件信息
     * 如：oss:test/abc.jpg解析之后得到的数据
     * - file_save_type:oss
     * - file_category:test
     * - file_prefix:系统内置前缀 @see self::buildPrefix
     * - file_real_name:abc.jpg
     * @param  string $string 文件查询id
     * @return [type]         [description]
     */
    public static function parseQueryId($string){
        $typeList = implode('|', ConstMap::getConst('file_save_type', true));
        if(preg_match("/^({$typeList}):{1}(.+)@(.+):{1}(.+)/", $string, $matches)){
            $fileCondition = [];
            $fileCondition['file_category'] = ltrim(trim($matches[2], '/'), '/');
            $fileCondition['file_save_type'] = $matches[1];
            $fileCondition['file_prefix'] = ltrim(trim($matches[3], '/'), '/');
            $fileCondition['file_real_name'] = basename(trim($matches[4]));

            return $fileCondition;
        }else{
            return [];
        }
    }

    /**
     * 获取文件的分片临时目录
     * @param  FileTask $fileTask 文件任务对象
     * @return string 文件分片临时目录
     */
    public static function getFileChunkDir(FileTask $fileTask){
        return Yii::getAlias('@app/runtime/file_chunk/') . $fileTask->file_task_code;
    }

    /**
     * 获取存储对象
     * @param  string $type 文件存储类型
     * @return object    文件存储对象

     */
    public static function getSaveMedium($type){
        switch ($type) {
            case Disk::NAME:
                return Yii::$app->filedisk;
            case Oss::NAME:
                return Yii::$app->fileoss;
            default:
                throw new InvalidParamException(Yii::t('app', "{$type} 不支持的存储类型"));
                break;
        }
    }
    /**
     * 创建分片目录
     * @param  FileTask $fileTask 文件分片任务对象
     * @return string            返回创建完成的分片目录
     * @see self::getFileChunkDir
     */
    public static function buildFileChunkDir(FileTask $fileTask){
        $baseDir = dirname(self::getFileChunkDir($fileTask));
        if(!is_dir($baseDir)){
            FileHelper::createDirectory($baseDir);
            //todo fix
            chmod($baseDir, 0777);
        }
        $chunkDir = self::getFileChunkDir($fileTask);
        if(!is_dir($chunkDir)){
            FileHelper::createDirectory($chunkDir);
            // todo fix
            chmod($chunkDir, 0777);
        }
        return $chunkDir;
    }


    /**
     * 构建文件查询id
     * @see self::parseQueryId
     * @param  File   $file 统一文件对象
     * 构成规则：
     * {$file_save_type}:{file_category/}{file_name}[.file_ext]
     * @return stirng       文件查询id
     */
    public static function buildFileQueryId(File $file){
        return $file->file_save_type . ':' .
               $file->file_category . '@' . $file->file_prefix . ':'
               . $file->file_real_name;
    }

    public static function buildFileQueryFromArr($fileInfo){
        return $fileInfo['file_save_type'] . ':' .
            $fileInfo['file_category'] . '@' . $fileInfo['file_prefix'] . ':'
            . $fileInfo['file_real_name'];
    }

    /**
     * 获取统一文件对象的访问地址
     * @see \lfile\models\drivers\Oss::buildFileUrl
     * @see \lfile\models\drivers\Disk::buildFileUrl
     * @param  \lfile\models\ar\File   $file 统一文件对象
     * @return 文件url地址       文件访问url
     */
    public static function buildFileUrl(File $file){
        return self::getSaveMedium($file->file_save_type)->buildFileUrl($file);
    }

    public static function buildFileUrlFromArr($fileInfo){
        if(!$fileInfo){
            return "";
        }
        return self::getSaveMedium($fileInfo['file_save_type'])->buildFileUrlFromArr($fileInfo);
    }

    /**
     * 通过数组数据获取文件的访问地址
     * 同 lfile\models\FileModel::buildFileUrl不同的时，这里参数不要传入统一文件对象，就能够获取文件的访问url，唯一不同的时，这个方法只能获取
     * 公开文件的url,即file_is_private = 0
     * @param  array $fileInfo 文件信息
     * @return string           文件访问url
     */
    static private $tmpFile = null;
    public static function buildFileUrlStatic($fileInfo){
        if(!$fileInfo){
            return "";
        }
        $fileInfo['file_medium_info'] = json_encode(self::getSaveMedium($fileInfo['file_save_type'])->buildMediumInfo());
        return static::buildFileUrlFromArr($fileInfo);
    }

    /**
     * 构建文件在各个存储中的实际存储路径
     * disk为本地硬盘的存储，oss存储没有路径，但是路径可以用于模拟分类
     * @param  File   $file 统一文件独享
     * @return string       返回文件存储路径
     */
    public static function buildFileSavePath(File $file){
        return $file->file_prefix . '/' . $file->file_real_name;
    }

    /**
     * 构建文件存储的文件名称
     * 如果文件名不存在file_ext将没有文件后缀，本方法不会解析文件流来获取文件后缀
     * @param  File   $file 统一文件对象
     * @return string       文件名称
     */
    protected static function buildFileRealName(File $file){
        return             $file->file_real_name ? $file->file_real_name : md5(
                             $file->file_md5_value .
                             microtime(true)
                           ) .
                           ($file->file_ext ? '.' . $file->file_ext : '')
                           ;
    }





}
