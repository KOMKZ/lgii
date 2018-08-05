<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%skill_tag}}".
 *
 * @property int $skt_id 主键
 * @property int $sk_id 实体
 * @property string $skt_name 标签名称
 *
 * @property Skill $sk
 */
class SkillTag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%skill_tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sk_id', 'skt_name'], 'required'],
            [['sk_id'], 'integer'],
            [['skt_name'], 'string', 'max' => 64],
            [['sk_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::className(), 'targetAttribute' => ['sk_id' => 'sk_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'skt_id' => 'Skt ID',
            'sk_id' => 'Sk ID',
            'skt_name' => 'Skt Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSk()
    {
        return $this->hasOne(Skill::className(), ['sk_id' => 'sk_id']);
    }
}
