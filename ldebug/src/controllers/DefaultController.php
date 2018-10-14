<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ldebug\controllers;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use ldebug\models\search\Debug;
use yii\web\Response;

/**
 * Debugger controller provides browsing over available debug logs.
 *
 * @see \ldebug\Panel
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $layout = 'main';
    /**
     * @var \ldebug\Module owner module.
     */
    public $module;
    /**
     * @var array the summary data (e.g. URL, time)
     */
    public $summary;

    /**
     * @var array
     */
    private $_manifest;


    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = [];
        foreach ($this->module->panels as $panel) {
            $actions = array_merge($actions, $panel->actions);
        }

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return parent::beforeAction($action);
    }

    public function getDb(){
        return Yii::$app->get($this->module->db);
    }

    public function getDataTable(){
        return $this->module->dataTable;
    }

    public function getIndexTable(){
        return $this->module->indexTable;
    }
    public static function formatIndexData($one){
        if(!$one){
            return [];
        }
        $one['mailFiles'] = unserialize($one['mailFiles']);
        return $one;
    }

    public function findIndexData(){
        return (new Query())
               ->from($this->getIndexTable());
    }

    public function getDataFromTag($tag){
        $data = (new Query())->from($this->getDataTable())->where(['tag' => $tag])->one($this->getDb());
        if(!$data){
            return [];
        }
        return unserialize($data['data']);
    }

    public function getLatestTagIndex(){
        return static::formatIndexData((new Query())->from($this->getIndexTable())
                            ->orderBy(['time' => SORT_DESC])
                            ->one($this->getDb()));
    }

    public function getLastTagIndex($num = 11){
        $data = (new Query())->from($this->getIndexTable())
                             ->orderBy(['id' => SORT_DESC])
                             ->limit($num)
                             ->all($this->getDb());
        foreach($data as $key => $item){
            $data[$key]['mailFiles'] = static::formatIndexData($item);
        }
        return $data;
    }

    public function actionIndex()
    {
        $searchModel = new Debug();
        $query = static::findIndexData();
        $dataProvider = $searchModel->search($_GET, $query, $this->getDb());

        // load latest request
//        $tags = array_keys($this->getManifest());
//        $tag = reset($tags);
        $tag = ArrayHelper::getValue($this->getLatestTagIndex(), 'tag', '');
        $this->loadData($tag);

        return $this->render('index', [
            'panels' => $this->module->panels,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'manifest' => $dataProvider->getModels(),//$this->getManifest(),
        ]);
    }

    /**
     * @see \ldebug\Panel
     * @param string|null $tag debug data tag.
     * @param string|null $panel debug panel ID.
     * @return mixed response.
     * @throws NotFoundHttpException if debug data not found.
     */
    public function actionView($tag = null, $panel = null)
    {
        if ($tag === null) {
//            $tags = array_keys($this->getManifest());
//            $tag = reset($tags);
            $tag = ArrayHelper::getValue($this->getLatestTagIndex(), 'tag', '');
        }
        $this->loadData($tag);
        if (isset($this->module->panels[$panel])) {
            $activePanel = $this->module->panels[$panel];
        } else {
            $activePanel = $this->module->panels[$this->module->defaultPanel];
        }

        if ($activePanel->hasError()) {
            Yii::$app->errorHandler->handleException($activePanel->getError());
        }

        return $this->render('view', [
            'tag' => $tag,
            'summary' => $this->summary,
            'manifest' => $this->getLastTagIndex(),//$this->getManifest(),
            'panels' => $this->module->panels,
            'activePanel' => $activePanel,
        ]);
    }

    public function actionToolbar($tag)
    {
        $this->loadData($tag, 5);

        return $this->renderPartial('toolbar', [
            'tag' => $tag,
            'panels' => $this->module->panels,
            'position' => 'bottom',
        ]);
    }

    public function actionDownloadMail($file)
    {
        $filePath = Yii::getAlias($this->module->panels['mail']->mailPath) . '/' . basename($file);

        if ((mb_strpos($file, '\\') !== false || mb_strpos($file, '/') !== false) || !is_file($filePath)) {
            throw new NotFoundHttpException('Mail file not found');
        }

        return Yii::$app->response->sendFile($filePath);
    }

    /**
     * @param bool $forceReload
     * @return array
     */
    protected function getManifest($forceReload = false)
    {
        if ($this->_manifest === null || $forceReload) {
            if ($forceReload) {
                clearstatcache();
            }
            $indexFile = $this->module->dataPath . '/index.data';

            $content = '';
            $fp = @fopen($indexFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $content = fread($fp, filesize($indexFile));
                @flock($fp, LOCK_UN);
                fclose($fp);
            }

            if ($content !== '') {
                $this->_manifest = array_reverse(unserialize($content), true);
            } else {
                $this->_manifest = [];
            }
        }

        return $this->_manifest;
    }

    /**
     * @param string $tag debug data tag.
     * @param int $maxRetry maximum numbers of tag retrieval attempts.
     * @throws NotFoundHttpException if specified tag not found.
     */
    public function loadData($tag, $maxRetry = 0)
    {
        $data = $this->getDataFromTag($tag);
        if(!$data){
            throw new NotFoundHttpException("Unable to find debug data tagged with '$tag'.");
        }
        $exceptions = $data['exceptions'];
        foreach ($this->module->panels as $id => $panel) {
            if (isset($data[$id])) {
                $panel->tag = $tag;
                $panel->load(unserialize($data[$id]));
            }
            if (isset($exceptions[$id])) {
                $panel->setError($exceptions[$id]);
            }
        }
        $this->summary = $data['summary'];
        return ;
        // retry loading debug data because the debug data is logged in shutdown function
        // which may be delayed in some environment if xdebug is enabled.
        // See: https://github.com/yiisoft/yii2/issues/1504
        for ($retry = 0; $retry <= $maxRetry; ++$retry) {
            $manifest = $this->getManifest($retry > 0);
            if (isset($manifest[$tag])) {
                $dataFile = $this->module->dataPath . "/$tag.data";
                $data = unserialize(file_get_contents($dataFile));
                $exceptiondds = $data['exceptions'];
                foreach ($this->module->panels as $id => $panel) {
                    if (isset($data[$id])) {
                        $panel->tag = $tag;
                        $panel->load(unserialize($data[$id]));
                    }
                    if (isset($exceptions[$id])) {
                        $panel->setError($exceptions[$id]);
                    }
                }
                $this->summary = $data['summary'];

                return;
            }
            sleep(1);
        }

        throw new NotFoundHttpException("Unable to find debug data tagged with '$tag'.");
    }
}
