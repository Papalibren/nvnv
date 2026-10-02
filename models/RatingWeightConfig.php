<?php

namespace app\models;

class RatingWeightConfig extends BaseModel
{
    public static function tableName(): string
    {
        return 'rating_weight_config';
    }

    public static function current(): self
    {
        return self::findOne(1) ?? new self([
            'course_weight' => 28, 'tutoring_weight' => 12,
            'public_task_weight' => 32, 'public_exam_weight' => 28,
        ]);
    }
}