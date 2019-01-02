<?php
namespace lfile\models\drivers;

use lfile\models\FileModel;
use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;
use lfile\models\ar\File;
use yii\helpers\FileHelper;
/**
 *
 */
class Disk extends Model implements SaveMediumInterface
{
    /**
     * 存储类型的名称
     * @var string
     */
    CONST NAME = 'disk';

    /**
     * 本地存储的根目录
     * @see \lfile\models\drivers\setBase
     * @var string
     */
    protected $base = '';

    /**
     * 本地存储目录的权限设置
     * @var integer
     */
    public $dirMode = 0755;

    /**
     * 本地存储文件的权限设置
     * @var integer
     */
    public $fileMode = 0755;

    /**
     * 本地存储文件的域名设置
     * @var string
     */
    public $host = "";

    /**
     * 本地存储文件域名路径设置
     * @var string
     */
    public $urlRoute = "";

    /**
     * 本地存储根目录set方法
     * 这个参数时必须设置的，不设置的话会抛出错误
     * @param string 路径
     */
    public function setBase($value){
        if(!is_dir($value)){
            throw new InvalidConfigException(Yii::t('app',"{$value} 路径不存在"));
        }

        if(!is_writable($value)){
            throw new InvalidConfigException(Yii::t('app', "{$value} 对象没有写权限"));
        }
        $this->base = rtrim($value, '/');
    }

    public function getBase(){
        return $this->base;
    }

    /**
     * 构建文件的访问url
     * @param  \lfile\models\ar\File   $file   统一文件对象
     * @param  array  $params [description]
     * @return string         文件访问url
     */
    public function buildFileUrl(File $file, $params = []){
        return sprintf("%s/%s?query_id=%s", $this->host, $this->urlRoute, urlencode($file->file_query_id));
        $apiUrlManager = Yii::$app->apiurl;
        $apiUrlManager->hostInfo = $this->host;
        return $apiUrlManager->createAbsoluteUrl([$this->urlRoute, 'query_id' => $file->file_query_id]);
    }

    public function buildFileUrlFromArr($fileInfo){
        return sprintf("%s/%s?query_id=%s", $this->host, $this->urlRoute, urlencode(FileModel::buildFileQueryFromArr($fileInfo)));
    }

    /**
     * 通过直接复制来保存一个统一文件对象
     * @param  File   $targetFile 目标文件对象
     * @param  File   $originFile 原始文件对象
     * @return [type]             目标文件对象
     */
    public function saveByCopy(File $targetFile, File $originFile){
        return $targetFile;
    }

    /**
     * 保存一个统一文件对象到本地存储中
     * 注意该对象最好是通过\lfile\models\FileModel::createFile创建得到
     * @param  File   $file 统一文件对象
     * @return File
     */
    public function save(File $file){
        $savePath = $this->base . '/' . $file->getFileSavePath();
        $saveDir = dirname($savePath);
        if(!is_dir($saveDir)){
            FileHelper::createDirectory($saveDir);
            chmod($saveDir, $this->dirMode);
        }
        copy($file->file_source_path, $savePath);
        chmod($savePath, $this->fileMode);
        return $file;
    }
    /**
     * 构造本地存储的存储元信息
     * @return array
     */
    public function buildMediumInfo(){
        return [];
    }
}
