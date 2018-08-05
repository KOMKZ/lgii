<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\User;
use app\models\UserDetail;
use yii\helpers\Inflector;

class UserController extends Controller{
	public function actionIndex(){
		$selectedfields = [
			"u_id" => '',
			'u_name' => '',
			'ud_id' => '',
			'ud_long_intro' => '',
			'user_skills.us_id' => '',
			'user_skills.u_id' => '',
			'user_skills.sk_id' => '',
			'user_skills.sk_name' => '',
			'user_skills.skill_tag.skt_id' => '',
			'user_skills.skill_tag.sk_id' => '',
			'user_skills.skill_tag.skt_name' => ''
		];


		$fields = [
			"*",
			"user_skills",
		];
		$userQuery = User::find()->asArray();

		$userDetailTable = UserDetail::tableName();
		$userTable = User::tableName();

		$userQuery->from([
			"u" => $userTable,
		]);
		$userQuery->select([
			'u.*',
			'ud.ud_long_intro',
		]);



		$userQuery->leftJoin($userDetailTable . ' as ud ', "ud.u_id = u.u_id");
		list($fields, $withParams) = $this->buildDynamicWithParam([
			"u_id",
			'u_name',
			'ud_id',
			'ud_long_intro',
			'user_skills.us_id',
			'user_skills.u_id',
			'user_skills.sk_id',
			'user_skills.sk.sk_id',
			'user_skills.sk.sk_name',
			'user_skills.sk.skill_tags.skt_id',
			'user_skills.sk.skill_tags.sk_id',
			'user_skills.sk.skill_tags.skt_name',

		]);
		$result = $userQuery->with($withParams)->asArray()->all();
		console($result);
		return 1;
		$relation = [];

		$buildedWithParam = [];
		$buildedFields = [
			"us_id",
			"u_id",
			"sk_id",
		];
		$relation['user_skills'] =  call_user_func_array(function($params){
			return function($query) use ($params){
				if(!empty($params['fields'])){
					$query->select($params['fields']);
				}
				if(!empty($params['with'])){
					$query->with($params['with']);
				}
			};
		}, [[
			'fields' => $buildedFields,
			'with' => $buildedWithParam
		]]);

		$userQuery->with($relation);




		$result = $userQuery->all();
		console($result);
	}
	public function parseFields($fields){
		$parFields = [];
		$subFields = [];
		foreach($fields as $name){
			if(false === $pos = strpos($name, '.')){
				$parFields[] = $name;
				continue;
			}

			$subFields[substr($name, 0, $pos)][] = substr($name, $pos + 1);
		}
		return [$parFields, $subFields];
	}
	public function buildDynamicWithParam($fields){
		$with = [];
		list($parFields, $subFields) = $this->parseFields($fields);
		foreach($subFields as $relationName => $relationFields){
			list($buildedFields, $buildedWithParam) = $this->buildDynamicWithParam($relationFields);
			$with[$relationName] =  call_user_func_array(function($params){
				return function($query) use ($params){
					if(!empty($params['fields'])){
						$query->select($params['fields']);
					}
					if(!empty($params['with'])){
						$query->with($params['with']);
					}
				};
			}, [[
				'fields' => $buildedFields,
				'with' => $buildedWithParam
			]]);
		}
		return [$parFields, $with];
	}
}
