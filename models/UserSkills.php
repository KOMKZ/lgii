<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_skills}}".
 *
 * @property int $us_id 主键
 * @property int $u_id 用户id
 * @property int $sk_id 技能id
 *
 * @property User $u
 * @property Skill $sk
 */
class UserSkills extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_skills}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['u_id', 'sk_id'], 'required'],
            [['u_id', 'sk_id'], 'integer'],
            [['u_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['u_id' => 'u_id']],
            [['sk_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::className(), 'targetAttribute' => ['sk_id' => 'sk_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'us_id' => 'Us ID',
            'u_id' => 'U ID',
            'sk_id' => 'Sk ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU()
    {
        return $this->hasOne(User::className(), ['u_id' => 'u_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSk()
    {
        return $this->hasOne(Skill::className(), ['sk_id' => 'sk_id']);
    }
}
