<?php

namespace app\controllers;

use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\User;
use app\models\UserDetail;
use yii\helpers\Inflector;

class UserController extends Controller{











	public function actionIndex(){
        $selectFields = [
            "u_id",
            'u_name',
            '[ud]ud_id',

            '[ud]ud_long_intro',

            'user_skills.us_id',
            'user_skills.u_id',
            'user_skills.sk_id',
            'user_skills.[s]sk_name',


            'user_skills.sk.sk_id',
            'user_skills.sk.sk_name',

            'user_skills.sk.skill_tags.skt_id',
            'user_skills.sk.skill_tags.sk_id',
            'user_skills.sk.skill_tags.skt_name',
        ];
        $query = User::getFullInfo($selectFields);
        $query->andWhere(['=', 'u.u_id', '1']);
        $result = $query->all();


	}


}
