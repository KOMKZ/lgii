<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 下午1:58
 */
namespace lsite\controllers;

use lsite\models\action\ActionComponent;
use Yii;
use lbase\Controller;
use yii\data\ActiveDataProvider;

class LactionController extends Controller{

    /**
     * @api get,/laction,ActionLog,查询动作日志
     * - al_action optional,integer,in_query,动作编号
     * - al_obj_id optional,integer,in_query,动作作用id
     * - al_uid optional,integer,in_query,动作操作用户id
     *
     * @return #global_res
     * - data object#action_item_list,返回动作列表对象
     *
     */
    public function actionList(){
        $params = Yii::$app->request->get();
        $query = ActionComponent::findFull($params);
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->succItems(ActionComponent::formatList($provider->getModels()));
    }
}
/**
 * @def #action_item_list
 * - total_count integer,总数量
 * - items array#action_item,动作对象
 *
 * @def #action_item
 * - al_id integer,主键
 * - al_opr_uid integer,操作用户id
 * - al_action integer,动作名称
 * - al_obj_id integer,动作关联对象id
 * - al_data string,关联记录参数，json类型
 * - al_created_at integer,创建时间
 * - al_updated_at integer,更新时间
 * - al_opr_uname integer,操作用户名称
 * - al_action_name string,动作描述
 *
 */

