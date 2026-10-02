<?php

namespace app\models;

use yii\db\ActiveRecord;

abstract class BaseModel extends ActiveRecord
{
    /**
     * Метки для отображения статусов, ролей и т.д.
     * Переопределяется в дочерних моделях.
     */
    public static function getLabels(): array
    {
        return [];
    }

    /**
     * Получить метку по ключу
     */
    public static function getLabel(string $key): string
    {
        return static::getLabels()[$key] ?? $key;
    }
}