<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\base\InvalidConfigException;
use yii\rbac\DbManager;
use yii\console\controllers\MigrateController;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use luser\models\user\UserModel;

/**
 * 根据接口构造权限数据
 */
class RbacController extends Controller{
	protected $db = null;
	public $api_base = [
        "@lgoods/controllers" => "\lgoods\controllers",
        "@lfile/controllers" => "\lfile\controllers",
        "@lsite/controllers" => "\lsite\controllers",
        "@luser/controllers" => "\luser\controllers",
    ];
	public $rbac_def_file = "@app/config/api-rbac-def-data.php";
	public $rbac_all_yaml_file = "@app/config/api-rbac-all-data.yaml";

	/**
	 * 根据条件注入筛选用户注入到角色中
	 * @param  string $roleName  [description]
	 * @param  string $condition [description]
	 * @return [type]            [description]
	 */
	public function actionAssign($roleName, $condition){
		$users = UserModel::find()
						   ->where($condition)
						   ->select(['u_id', 'u_username', 'u_email'])
						   ->asArray()
						   ->all();
		$role = Yii::$app->authManager->getRole($roleName);
		if(!$role){
			return Console::stderr(sprintf("指定的role %s 不存在\n", $roleName));
		}
		$t = Yii::$app->db->beginTransaction();
		try {
			foreach($users as $user){
				Yii::$app->authManager->assign($role, $user['u_id']);
				echo sprintf("分配用户到角色%s %-5s %-15s %-15s\n",
				$roleName, $user['u_id'], $user['u_username'], $user['u_email']
				);
			}
			$t->commit();
		} catch (\Exception $e) {
			$t->rollback();
			throw $e;
			Console::stderr("失败已经回滚\n");
		}
	}

	public function actionInstallRbacData($fresh = true, $withAssign = true){
		$fresh ? $this->actionGeneRbacData() : null;
		// 将yaml文件导入进来
		$rbacData = spyc_load_file($this->getRbacDataFile());
		if(empty($rbacData) ||
		empty($rbacData['roles']) || empty($rbacData['permissions']) || empty($rbacData['assign'])
		){
			return Console::stderr("数据不完整");
		}
		$t = Yii::$app->db->beginTransaction();
		$rbacMg = Yii::$app->authManager;
		try {
			// 卸载掉原来的
			$this->deleteTable($rbacMg->itemTable);
			$this->deleteTable($rbacMg->itemChildTable);
			$this->deleteTable($rbacMg->assignmentTable);
			$this->deleteTable($rbacMg->ruleTable);



			// 安装角色和权限
			foreach ($rbacData['roles'] as $key => $item) {
				list($name, $label) = explode('@', $item);
				$item = $rbacMg->createRole($name);
				$item->description = $label;
				$rbacMg->add($item);
				$rbacData['roles'][$name] = $item;
			}
			$permNames = [];
			foreach ($rbacData['permissions'] as $key => $item) {
				list($name, $label) = explode('@', $item);
				$item = $rbacMg->createPermission($name);
				$item->description = $label;
				$rbacMg->add($item);
				$rbacData['permissions'][$name] = $item;
				$permNames[$name] = null;
			}
			// 注入权限
			foreach($rbacData['assign'] as $key => $item){
				list($roleName, $permName) = explode('@', $item);
				$role = $rbacData['roles'][$roleName];
				$perm = $rbacData['permissions'][$permName];
				if(!$rbacMg->hasChild($role, $perm)){
					$rbacMg->addChild($role, $perm);
				}
			}
			Console::stdout("install rbac data ok.  ^_^\n");
			if($withAssign){
				// todo more 灵活
				$this->actionAssign('root', "u_email='784248377@qq.com'");
                $this->actionAssign('normal', "u_email='784248378@qq.com'");

			}
			$t->commit();
		} catch (\Exception $e) {
			$t->rollback();
			throw $e;
		}


	}
	/**
	 * 生成rbac数据
	 * @return [type] [description]
	 */
	public function actionGeneRbacData(){
		// 初始化数据环境
		// $hasTable = Yii::$app->db->createCommand("select * from {{%migration}} where version = 'm140506_102106_rbac_init'")->queryAll();
		// if($hasTable){
		// 	$this->removeRbacData();
		// }else{
		// 	system(sprintf("%s/yii migrate/up -p=@app/rbac --interactive=0", getcwd()));
		// }

		// 构造权限数据
		$exclude = [
			'api/error' => null
		];
		$permissionsFromApi = $this->parsePermFromApi($exclude);
		$permissionsFromDef = $this->getPermFromDef();
		$permissions = ArrayHelper::merge($permissionsFromApi, $permissionsFromDef);
		// 构造角色数据
		$roles = $this->getRoleFromDef();
		// 构造权限分配
		$assign = $this->getAssignFromDef();
		$oldData = ['roles' => [], 'permissions' => [], 'assign' => []];
		if(file_exists(Yii::getAlias($this->rbac_all_yaml_file))){
			$data = spyc_load_file(Yii::getAlias($this->rbac_all_yaml_file));
			foreach($data['roles'] as $index => $item){
				$item = explode('@', $item);
				$oldData['roles'][$item[0]] = $item;
			}
			foreach($data['permissions'] as $index => $item){
				$item = explode('@', $item);
				$oldData['permissions'][$item[0]] = $item;
				$permNames[$item[0]] = null;
			}

			foreach($data['assign'] as $index => $item){
				list($roleName, $permName) = explode('@', $item);
				$oldData['assign'][$roleName . '-' . $permName] = [$roleName, $permName];

			}
		}else{
            $permNames = [];
        }
		$rbacResult = array_merge($oldData, [
			'roles' => $roles,
			'permissions' => $permissions,
			'assign' => $assign
		]);
		$rbacData = [
			'roles' => [],
			'permissions' => [],
			'assign' => [],
		];
		foreach($rbacResult['roles'] as $item){
			$rbacData['roles'][] = implode('@', $item);
		}
		foreach($rbacResult['permissions'] as $item){
			$rbacData['permissions'][] = implode('@', $item);
		}
		foreach($rbacResult['assign'] as $item){
			$pattern = false;
			list($roleName, $permName) = $item;
			if(preg_match('/([\w\-\_\d\*]+)\/([\d\w\*\-\_]+)/', $permName, $matches)){
				list(,$mName, $aName) = $matches;
				if('*' == $mName || '*' == $aName){
					$pattern = sprintf('/^(%s)\/(%s)$/',
								'*' == $mName ? '[\w\-\_\d]+?' : $mName,
								'*' == $aName ? '[\w\-\_\d]+?' : $aName);
				}
			}
			if($pattern){
				foreach($permNames as $permName => $null){
					if(preg_match($pattern, $permName, $matches)){
						$rbacData['assign'][] = $roleName . '@' .  $permName;
					}
				}
			}else{
				$rbacData['assign'][] = $roleName . '@' .  $permName;
			}
		}
		file_put_contents($this->getRbacDataFile(), spyc_dump($rbacData));
		echo sprintf("save file in: %s\n", $this->getRbacDataFile());
	}
	public function getRbacDataFile(){
		return Yii::getAlias($this->rbac_all_yaml_file);
	}
	protected function getAssignFromDef(){
		$defs = require(Yii::getAlias($this->rbac_def_file));
		$assign = [];
		foreach(ArrayHelper::getValue($defs, 'assign', []) as $item){
			$assign[$item[0] . '-' . $item[1]] = $item;
		}
		return $assign;
	}
	protected function getPermFromDef(){
		$defs = require(Yii::getAlias($this->rbac_def_file));
		$perms = ArrayHelper::getValue($defs, 'permissions', []);
		return ArrayHelper::index($perms, 0);
	}
	protected function getRoleFromDef(){
		$defs = require(Yii::getAlias($this->rbac_def_file));
		$perms = ArrayHelper::getValue($defs, 'roles', []);
		return ArrayHelper::index($perms, 0);
	}
	protected function parsePermFromApi($exclude = []){
        $apis = [];
        foreach($this->api_base as $apiBase => $namespace){
            $apiDir = Yii::getAlias($apiBase);
            foreach(glob($apiDir . '/*') as $item){
                $controllerName = preg_replace('/Controller.php/', '', basename($item));
                $controllerId = strtolower(trim(preg_replace('/([A-Z][a-z0-9]*)/', "$1-", $controllerName), '-'));
                $controllerClass = $namespace . "\\" . $controllerName;
                $reflection = new \ReflectionClass($namespace . '\\' . $controllerName . 'Controller');
                foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
                    if(preg_match('/action([A-Z][a-z0-9A-Z]*)/', $method->getName(), $matches)){
                        $actionId = strtolower(trim(preg_replace('/([A-Z][a-z0-9]*)/', "$1-", $matches[1]), '-'));
                        $api = sprintf("%s/%s", $controllerId, $actionId);
                        $des = "";
                        $docblock = $method->getDocComment();
                        if($docblock){
                            if(preg_match('/@api\s+[^\n]+,\s*([\s\S]+?)[\n\s]+/u', $docblock, $matches)){
                                $des = $matches[1];
                            }
                        }
                        if(!array_key_exists($api, $exclude)){
                            $apis[$api] = [$api, $des];
                        }
                    }
                }
            }
        }
		return $apis;
	}
	protected function removeRbacData(){
		$authManager = $this->getAuthManager();
		$this->db = $authManager->db;
		$this->dropTable($authManager->assignmentTable);
		$this->dropTable($authManager->itemChildTable);
		$this->dropTable($authManager->itemTable);
		$this->dropTable($authManager->ruleTable);
		$this->delete("{{%migration}}", "version = 'm140506_102106_rbac_init'");
	}
	protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }
        return $authManager;
    }

	public function dropTable($table)
	{
		echo "    > drop table $table ...";
		$time = microtime(true);
		Yii::$app->db->createCommand()->dropTable($table)->execute();
		echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
	}



	public function deleteTable($table, $condition = '', $params = [])
    {
        echo "    > delete from $table ...";
        $time = microtime(true);
        Yii::$app->db->createCommand()->delete($table, $condition, $params)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }
}
