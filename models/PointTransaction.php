<?php

namespace app\models;

class PointTransaction extends BaseModel
{
    const SOURCE_HOMEWORK = 'homework';
    const SOURCE_EXAM     = 'exam';

    public static function tableName(): string
    {
        return 'point_transaction';
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

    public function getStudent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'student_id']);
    }

    public static function getStudentTotal(int $studentId): int
    {
        return (int) static::find()
            ->where(['student_id' => $studentId])
            ->sum('points');
    }

    public static function getGroupRanking(int $groupId): array
    {
        return static::find()
            ->select(['student_id', 'SUM(points) as total_points'])
            ->joinWith('student')
            ->where(['group_student.group_id' => $groupId])
            ->innerJoin('group_student', 'group_student.student_id = point_transaction.student_id')
            ->groupBy('student_id')
            ->orderBy('total_points DESC')
            ->asArray()
            ->all();
    }
}