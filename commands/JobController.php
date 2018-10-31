<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Post;
use yii\helpers\ArrayHelper;

class JobController extends Controller{



    public function actionIndex(){
        $sql = "
        select * from user where u_id <= 10;
        ";
        $t = Yii::$app->db->beginTransaction();
        $r = Yii::$app->db->createCommand($sql)->queryAll();
        print_r($r);
        sleep(4);
        $r = Yii::$app->db->createCommand($sql)->queryAll();
        print_r($r);
        $t->commit();
    }

    public function actionA1($value){
        $sql = "
        update user set u_name = '{$value}' where u_id = 1;
        ";
        $t = Yii::$app->db->beginTransaction();
        $r = Yii::$app->db->createCommand($sql)->execute();
        $t->commit();
    }

    public function actionA2($value){
        $sql = "
        insert into user values (null, '{$value}');
        ";
        $t = Yii::$app->db->beginTransaction();
        $r = Yii::$app->db->createCommand($sql)->execute();
        $t->commit();
    }

    static public $params = [];
    public static function getParams($name){
        if(static::$params){
            return static::$params;
        }
        return static::$params = require(Yii::getAlias('@app/config/es-defs/' . $name . ".php"));
    }
    public function actionRun($version){
        $this->installData($version);
        $this->actionInstallIndex($version);
        $this->actionUpdatePostData($version);
//        $this->actionSearchAll($name, $text);
    }
    public function actionSearchAll($version, $text, $type = ''){
        $setting = static::getParams($version);

        $query = $setting['query'];
        $params = [
            'index' => 'blog',
            'type' => 'post',
            'body' => [
                'query' => call_user_func_array($query, [$text]),
                'sort' => [
                    ['_score' => ['order' => 'desc']],
                    ['created_at' => ['order' => 'desc']],
                ],
                'highlight' => [
                    'number_of_fragments' => 2,
                    'fragment_size' => 60,
                    "pre_tags" => ["\033[0m\033[31m"],
                    "post_tags" => ["\033[0m"],
                    'fields' => [
                        'title' => new \StdClass(),
                        'content' => new \StdClass(),
                        'create_uname' => new \StdClass()
                    ]
                ]
            ]
        ];
        $res = $this->getConnection()->search($params);
        echo "源文档如下\n";
        foreach($res['hits']['hits'] as $item){
           foreach($item['_source'] as $name => $value){
               echo sprintf("%-15s%s\n", $name . ':', $value);
               if(isset($item['highlight'][$name])){
                   echo sprintf("%-15s%s\n", '', implode(',', $item['highlight'][$name]));
               }
           }
           echo "\n";
        }
        echo "得分如下\n";
        echo sprintf("%-3s%-15s%-12s%-10s%s\n", 'id','type', 'score', 'h_count', 'text');
        foreach($res['hits']['hits'] as $item){
            echo sprintf("%-3s%-15s%-12s%-10s%s\n", $item['_id'], $type, $item['_score'], count($item['highlight']), $text);
        }
//        print_r($res);

    }
    public function actionUpdatePostData($version){
        $setting = static::getParams($version);

        $pdo = Yii::$app->db->getMasterPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $conn = $this->getConnection();
        $uresult = $pdo->query($this->getLatestPost());
        if ($uresult) {
            $params = ['body' => []];
            $perNum = 100;
            $n = 0;
            while ($row = $uresult->fetch(\PDO::FETCH_ASSOC)) {
                $params['body'][] = ['index' => ['_index' => 'blog', '_type' => 'post', '_id' => $row['id']]];
                $params['body'][] = [
                    'created_at' => $row['created_at'],
                    'id' => $row['id'],
                    'create_uname' => $row['create_uname'],
                    'title' => $row['title'],
                    'content' => $row['content'],
                ];
                $n++;
                if($n == $perNum){
                    $r = $conn->bulk($params);
                    $params = ['body' => []];
                    $n = 0;
                    echo sprintf("affects:%s\n", count($r['items']));
                }
            }
            if($n != 0){
                $r = $conn->bulk($params);
                $params = ['body' => []];
                $n = 0;
                echo sprintf("affects:%s\n", count($r['items']));
            }
        }
    }
    public function getLatestPost(){
        return "select * from post;";
    }
    public function getConnection(){
        return Yii::$app->get('es');
    }
    public function actionInstallIndex($version){
        $setting = static::getParams($version);

        $conn = $this->getConnection();
        $indexName = $setting['index'];
        $indexBody = $setting['body'];
        $mapping = $setting['mapping'];

        $indexDef = [
            'index' => $indexName,
            'body' => $indexBody
        ];
        $exists = $conn->indices()->exists(['index' => $indexName]);
        if($exists){
            $conn->indices()->delete(['index' => $indexName]);
        }
        $conn->indices()->create($indexDef);

        $conn->indices()->putMapping([
            'index' => $indexName,
            'type' => $mapping['name'],
            'body' => [
                $mapping['name'] => [
                    'properties' => $mapping['properties']
                ]
            ]
        ]);
    }
    public function getSettings(){
        return Yii::$app->params['indexes'];
    }
    public function installDb(){
        $sql = <<<EOS
drop table if exists `post`;
create table `post`(
  id int(10) unsigned not null AUTO_INCREMENT COMMENT 'id',
  title varchar(64) not null comment '标题名称',
  create_uname varchar(64) not null comment '用户昵称',
  content text not null comment '文章内容',
  created_at int(10) unsigned not null comment '创建时间',
  primary key (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='文章表';
EOS;
        Yii::$app->db->createCommand($sql)->execute();
    }
    public function getFaker($version){
        $setting = static::getParams($version);

        return $setting['data'];
    }
    public function installData($version){
        $this->installDb();
        Yii::$app->db->createCommand()->batchInsert(Post::tableName(), [
            'title', 'create_uname', 'content', 'created_at'
        ], $this->getFaker($version))->execute();
    }

}