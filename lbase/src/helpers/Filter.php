<?php
namespace lbase\base;
use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 *
 */
class Filter extends Object
{
	public $query;
	public $params;
	public $attributes = [];
	public function init(){
		parent::init();
		if(empty($this->params)){
			return ;
		}
		if(empty($this->query)){
			throw new InvalidConfigException(Yii::t('app', 'Attribute query cant not be empty.'));
		}
		$this->formatAttributes();

	}
	public function parse(){
		$params = $this->parseParams();
		if(empty($params)){
			return $this->query;
		}
		$conditions = [];
		foreach($params as $field => $conditionVal){
			$conditionDef = [];
			if(is_array($this->attributes[$field]) && (3 === count($this->attributes[$field]))){
				$conditionDef = $this->attributes[$field];
				if(is_string($conditionVal)){
					$conditionDef[2] = is_callable($conditionDef[2]) ?
									   $conditionDef[2]($conditionVal)
									   :
									   strtr($conditionDef[2], ['%s%' => $conditionVal]);
				}else{
					$conditionDef[2] = $conditionVal;
				}
			}elseif(is_string($this->attributes[$field])){
				$conditionDef = ['=', $this->attributes[$field], $conditionVal];
			}else{
				$conditionDef = ['=', $field, $conditionVal];
			}
			if(is_array($conditionDef[2])){
				$conditionDef[0] = 'in';
			}
			$conditions[] = $conditionDef;
			$this->query->andWhere($conditionDef);
		}
		return $this->query;
	}
	protected function formatAttributes(){
		$safeAttrs = $this->getSafeAttributes();
		if(!$this->attributes){
			$this->attributes = $safeAttrs;
			return ;
		}
		foreach($this->attributes as $name => $item){
			if(is_string($item)){
				$this->attributes[$item] = null;
				unset($this->attributes[$name]);
			}
		}
	}
	protected function getSafeAttributes(){
		return (new $this->query->modelClass)->getAttributes();
	}
	protected function parseParams(){
		if(empty($this->params)){
			return [];
		}
		foreach($this->params as $field => $param){
			if(!array_key_exists($field, $this->attributes) || !$param){
				unset($this->params[$field]);
				continue;
			}
			if(is_string($param)){
				$candicates = preg_split('/\s*,\s*/', $param, -1, PREG_SPLIT_NO_EMPTY);
				if(count($candicates) > 1){
					$this->params[$field] = $candicates;
				}
			}
		}
		return $this->params;
	}
}
