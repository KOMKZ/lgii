<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $u_id 主键
 * @property string $u_name 用户名称
 *
 * @property UserDetail $userDetail
 * @property UserSkills[] $userSkills
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['u_name'], 'required'],
            [['u_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'u_id' => 'U ID',
            'u_name' => 'U Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser_detail()
    {
        return $this->hasOne(UserDetail::className(), ['u_id' => 'u_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser_skills()
    {
        return $this->hasMany(UserSkills::className(), ['u_id' => 'u_id']);
    }
}
