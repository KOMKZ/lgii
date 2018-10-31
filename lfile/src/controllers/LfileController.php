<?php
namespace lfile\controllers;

use Yii;
use lbase\Controller;
use lfile\models\FileModel;
use lfile\models\query\FileQuery;
use lfile\models\ar\FileTask;
use lfile\models\ar\File;
use lbase\helpers\ArrayHelper;
use lfile\models\drivers\Disk;
use lfile\models\drivers\Oss;
use yii\web\NotFoundHttpException;
use yii\base\InvalidParamException;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;
use lbase\Filter;
/**
 *
 */
class LfileController extends Controller
{
    public function actionIndex(){

    }
    public function actionDelete(){
        $postData = Yii::$app->request->getBodyParams();
        if(empty($postData['file_ids']) || !is_array($postData['file_ids'])){
            return $this->succ(0);
        }
        $fileModel = new FileModel();
        $files = FileQuery::find()->andWhere(['in', 'file_id', $postData['file_ids']])->all();
        $succ = 0;
        foreach($files as $file){
            $result = $fileModel->deleteFile($file);
            if($result){
                $succ++;
            }
        }
        return $this->succ($succ);
    }
    public function actionList(){
        $getData = Yii::$app->request->get();
        $query = FileQuery::find();
		$defaultOrder = [
			'file_created_time' => SORT_DESC,
		];
        $filterParams = json_decode(ArrayHelper::getValue($getData, 'filters', ''), true);
		if(!empty($filterParams)){
			$query = (new Filter([
				'attributes' => [
                    'file_is_tmp',
                    'file_is_private',
                    'file_save_type',
                    'file_ext' => ['like', 'file_ext', '%s%'],
                    'file_save_name' => ['like', 'file_save_name', '%s%'],
                    'file_created_time_begin' => ['>=', 'file_created_time', function($dateStr){
						return strtotime($dateStr);
					}],
					'file_created_time_end' => ['<=', 'file_created_time', function($dateStr){
						return strtotime($dateStr);
					}],
				],
				'query' => $query,
				'params' => $filterParams
			]))->parse();
		}
		$provider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'defaultOrder' => $defaultOrder,
				'attributes' => [
					'file_created_time'
				]
			]
		]);
		return $this->succItems($provider->getModels(), $provider->totalCount);
    }

    public function actionChunkTaskCreate(){
        $fileModel = new FileModel();
        $post = Yii::$app->request->getBodyParams();
        // todo 检查 access_token 的合法性,应该在数据库中检查
        if(empty($post['access_token'])){
            return $this->error('', Yii::t('app', 'access_token不合法'));
        }
        $fileTask = $fileModel->createFileChunkedUploadTask($post);
        if(!$fileTask){
            list($code, $message) = $fileModel->getOneError();
            return $this->error($code, $message);
        }
        return $this->succ($fileTask->toArray());
    }

    /**
     * @api post,/files,File,上传一个文件
     * - file_save_name optional,string,in_body,文件保存名称
     * - file_is_tmp optional,integer,in_body,文件是否是临时文件
     * - file_valid_time optional,integer,in_body,仅当is_tmp为1的时候有效，如定义3600，说明文件在服务器的有效时间是3600秒
     * - file_category optional,string,in_body,文件分类信息
     *
     * @return #global_res
     * - data object#file_item,文件信息
     */
    public function actionCreate(){
        $post = Yii::$app->request->getBodyParams();
        $fileModel = new FileModel();
        if(!empty($post['file_md5_value'])){
            // 从文件md5值在服务端进行拷贝
            $file = FileQuery::find()->where(['file_md5_value' => $post['file_md5_value']])->one();
            if(!$file){
                return $this->error(404, Yii::t('app', "{$post['file_md5_value']}相关文件不存在"));
            }
            $fileCopy = $fileModel->createFile(array_merge($file->toArray(), $post), true);
            if(!$fileCopy){
                list($code, $message) = $fileModel->getOneError();
                return $this->error($code, $message);
            }

            $fileCopy = $fileModel->saveFileByCopy($fileCopy, $file);
            if(!$fileCopy){
                list($code, $message) = $fileModel->getOneError();
                return $this->error($code, $message);
            }

            $fileCopy = $fileModel->saveFileInDb($fileCopy);
            if(!$fileCopy){
                list($code, $message) = $fileModel->getOneError();
                return $this->error($code, $message);
            }
            return $this->succ($fileCopy->toArray());

        }elseif(empty($post['chunks'])){
            // 从文件流来上传, 不分片
            if(empty($_FILES) || empty($_FILES['file']) || $_FILES["file"]["error"]){
                return $this->error(null, Yii::t('app','没有文件数据'));
            }
            $post['file_save_name'] = empty($post['file_save_name']) ? ($_FILES['file']['name']) : $post['file_save_name'];
            $fileData = array_merge([
                'file_source_path' => $_FILES['file']['tmp_name']
            ], $post);
            $file = $fileModel->createFile($fileData);
            if(!$file){
                list($code, $message) = $fileModel->getOneError();
                return $this->error($code, $message);
            }
            $file = $fileModel->saveFile($file);
            if(!$file){
                list($code, $message) = $fileModel->getOneError();
                return $this->error($code, $message);
            }
            $file = $fileModel->saveFileInDb($file);
            if(!$file){
                list($code, $message) = $fileModel->getOneError();
                return $this->error($code, $message);
            }
            return $this->succ($file->toArray());
        }else{
            // 文件流分片上传
            $post = Yii::$app->request->post();

            $fileResult = $fileModel->createFilePart($post, []);;
            if(!$fileResult){
                list($code, $error) = $fileModel->getOneError();
                return $this->error($code, $error);
            }
            if($fileResult instanceof File){
                return $this->succ($fileResult->toArray());
            }
            return $this->succ($fileResult);
        }
    }

    public function actionOutput($query_id){
        $get = Yii::$app->request->get();
        $fileInfo = FileModel::parseQueryId($query_id);
        $file = FileQuery::find()->where($fileInfo)->one();
        if(!$file){
            throw new NotFoundHttpException(Yii::t('app', "{$query_id} 文件不存在"));
        }
        if($file->file_is_private && (empty($get['signature']) || !FileModel::checkSignature($get['signature'], $get))){
            throw new ForbiddenHttpException(Yii::t('app', "您没有权限访问该文件"));
        }
        if(Disk::NAME == $file->file_save_type){
            return Yii::$app->response->sendFile($file->getFileDiskFullSavePath(), $file->file_save_name, ['inline' => true]);
        }elseif(Oss::NAME == $file->file_save_type){
            $url = $file->file_url;
            header("location:{$url}");
        }else{
            throw new InvalidParamException(Yii::t('app', "不支持的输出类型" . $file->file_save_type));
        }
    }
}

/**
 * @def #file_item
 * - file_query_id integer,文件索引id
 * - file_url string,文件url
 *
 */
