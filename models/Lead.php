<?php

namespace app\models;

class Lead extends BaseModel
{
    public static function tableName(): string
    {
        return 'lead';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['landing_id', 'name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 30],
            [['email'], 'email'],
            [['message'], 'string'],
            [['utm_source', 'utm_medium', 'utm_campaign'], 'string', 'max' => 100],
            [['is_processed'], 'boolean'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name'         => 'Имя',
            'phone'        => 'Телефон',
            'email'        => 'Email',
            'message'      => 'Сообщение',
            'is_processed' => 'Обработано',
            'created_at'   => 'Дата заявки',
        ];
    }

    public function getLanding(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Landing::class, ['id' => 'landing_id']);
    }
}