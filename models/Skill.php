<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%skill}}".
 *
 * @property int $sk_id 主键
 * @property string $sk_name 技能名称
 *
 * @property SkillTag[] $skillTags
 * @property UserSkills[] $userSkills
 */
class Skill extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%skill}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sk_name'], 'required'],
            [['sk_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sk_id' => 'Sk ID',
            'sk_name' => 'Sk Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSkill_tags()
    {
        return $this->hasMany(SkillTag::className(), ['sk_id' => 'sk_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser_skills()
    {
        return $this->hasMany(UserSkills::className(), ['sk_id' => 'sk_id']);
    }
}
