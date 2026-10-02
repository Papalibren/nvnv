<?php

namespace app\models;

class Group extends BaseModel
{
    public static function tableName(): string
    {
        return 'group';
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
            [['teacher_id', 'name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['teacher_id'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name'        => 'Название группы',
            'description' => 'Описание',
        ];
    }

    public function getTeacher(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }

    public function getStudents(): \yii\db\ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'student_id'])
                    ->viaTable('group_student', ['group_id' => 'id']);
    }

    public function getStudentCount(): int
    {
        return (int) $this->getStudents()->count();
    }

    public function hasStudent(int $studentId): bool
    {
        return $this->getStudents()->andWhere(['user.id' => $studentId])->exists();
    }
}