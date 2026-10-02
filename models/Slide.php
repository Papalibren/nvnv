<?php

namespace app\models;

class Slide extends BaseModel
{
    public static function tableName(): string
    {
        return 'slide';
    }

    public function getDeck(): \yii\db\ActiveQuery
    {
        return $this->hasOne(SlideDeck::class, ['id' => 'deck_id']);
    }
}