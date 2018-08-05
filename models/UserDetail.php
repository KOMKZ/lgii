<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_detail}}".
 *
 * @property int $ud_id 主键
 * @property int $u_id 用户id
 * @property string $ud_long_intro 用户长介绍
 *
 * @property User $u
 */
class UserDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['u_id', 'ud_long_intro'], 'required'],
            [['u_id'], 'integer'],
            [['ud_long_intro'], 'string'],
            [['u_id'], 'unique'],
            [['u_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['u_id' => 'u_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ud_id' => 'Ud ID',
            'u_id' => 'U ID',
            'ud_long_intro' => 'Ud Long Intro',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU()
    {
        return $this->hasOne(User::className(), ['u_id' => 'u_id']);
    }
}
