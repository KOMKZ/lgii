<?php
namespace lgoods\models\category;

use lfile\models\FileModel;
use Yii;
use lgoods\models\category\GoodsClassification;
use yii\base\Model;
use yii\helpers\Url;

/**
 *
 */
class ClassificationModel extends Model
{
	public $maxLevel = 4;

	public function updateGoodsClassification($goodsCls, $data){
		$goodsCls->scenario = 'update';
		if(!$goodsCls->load($data, '') || !$goodsCls->validate()){
			$this->addErrors($goodsCls->getErrors());
			return false;
		}
        $goodsCls->update(false);
		return $goodsCls;
	}

	public function validateClsCreate($data, GoodsClassification $goodsCls){
		if(!$goodsCls->load($data) || !$goodsCls->validate()){
			return false;
		}
		return true;
	}

	public function removeClassification(GoodsClassification $goodsCls){
		$hasChild = GoodsClassification::find()
											->andWhere(['=', 'g_cls_pid', $goodsCls->g_cls_id])
											->asArray()
											->all();
		if($hasChild){
			$this->addError('', Yii::t('app', '指定的分类存在子分类，禁止删除'));
			return false;
		}
		// todo more check 必须检查是否有商品
		$goodsCls->delete();
		// todo more delete
		return true;
	}

	public function removeClsSafe($ids){
		// todo more delete
		return GoodsClassification::deleteAll(['g_cls_id' => $ids]);
	}

	public function createGoodsClassification($data){
        $goodsCls = new GoodsClassification();
        if(!$goodsCls->load($data, '') || !$goodsCls->validate()){
			$this->addErrors($goodsCls->getErrors());
			return false;
		}
		if(!empty($goodsCls->g_cls_pid)){
			$parents = static::findParentsById($goodsCls->g_cls_pid);
			if(count($parents) >= $this->maxLevel){
				$this->addError('', Yii::t('app', "分类层级不得超过{$this->maxLevel}级"));
				return false;
			}
		}
		$goodsCls->g_cls_created_at = time();
        $goodsCls->insert(false);
		return $goodsCls;
	}


    public static function find(){
        return GoodsClassification::find();
    }

    public static function findClsAsTree(){
	    $fModel = new FileModel();
        $allCls = [];
        foreach(self::find()->select([
            'g_cls_id',
            'g_cls_name',
            'g_cls_show_name',
            'g_cls_pid',
            'g_cls_img_id'
        ])->asArray()->all() as $cls){
            $cls['nodes'] = [];
            $cls['g_cls_img_url'] = $fModel->buildFileUrlStatic(FileModel::parseQueryId($cls['g_cls_img_id']));
//            $cls['text'] = $cls['g_cls_name'];
//            $cls['href'] = Url::to(['classification/update', 'id' => $cls['g_cls_id']]);
            $allCls[$cls['g_cls_id']] = $cls;
        }
        foreach($allCls as $index => $cls){
            if(0 == $cls['g_cls_pid']){
                continue;
            }
            $fatherId = $cls['g_cls_pid'];
            $curId = $cls['g_cls_id'];
            $fatherNode = $allCls[$fatherId];
            if(is_string($fatherNode)){
                $allCls[$index] = static::setAsLeave($allCls, $fatherNode, $cls);
            }else{
                $allCls[$fatherId]['nodes'][$curId] = $cls;
                $allCls[$index] = $fatherId . ',' . $curId;
            }
        }
        foreach($allCls as $index => $item){
            if(is_string($item)){
                unset($allCls[$index]);
            }
        }
        $allCls = static::covertToArray($allCls);
        return $allCls;
    }

    protected static function covertToArray($allCls){
        $result = [];
        foreach($allCls as $index => $cls){
            if(!empty($cls['nodes'])){
                $cls['nodes'] = static::covertToArray($cls['nodes']);
            }
            $result[] = $cls;
        }
        return $result;
    }

    protected static function setAsLeave(&$allCls, $pidPath, $cls){
        $pidPathData = explode(',', $pidPath);
        $id = array_shift($pidPathData);
        $target = &$allCls[$id];
        while($id = array_shift($pidPathData)){
            $target = &$target['nodes'][$id];
        }
        $target['nodes'][$cls['g_cls_id']] = $cls;
        return $pidPath . ',' . $cls['g_cls_id'];
    }


    public static function findChildrenByCls($cls){
        $children = [];
        $query = self::find()
            ->andWhere(['=', 'g_cls_pid', $cls->g_cls_id]);
        return $query;
    }


    /**
     * 根据一个分类id获取该分类的所有父类
     * @param  [type] $clsId [description]
     * @return [type]        [description]
     */
    public static function findParentsById($clsId, $fields = []){
        $parents = [];
        $one = static::find()
            ->select($fields)
            ->where([ 'g_cls_id' => $clsId])
            ->one();
        if(!$one){
            return null;
        }elseif(0 == $one->g_cls_pid){
            return [$one];
        }else{
            $r = self::findParentsById($one->g_cls_pid, $fields);
            if(null !== $r){
                return array_merge($r, [$one]);
            }
        }
    }
}
