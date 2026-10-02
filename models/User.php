<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

class User extends BaseModel implements IdentityInterface
{
    // Роли
    const ROLE_ADMIN   = 'admin';
    const ROLE_TEACHER = 'teacher';
    const ROLE_STUDENT = 'student';

    const MODE_SELF_STUDY = 'self_study';
    const MODE_TUTORED    = 'tutored';

    // Статусы
    const STATUS_PENDING  = 'pending';
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function tableName(): string
    {
        return 'user';
    }

    public function rules(): array
    {
        return [
            [['role', 'name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['username'], 'unique'],
            [['username'], 'string', 'max' => 50],
            [['telegram_chat_id', 'vk_id'], 'string', 'max' => 50],
            [['role'], 'in', 'range' => [self::ROLE_ADMIN, self::ROLE_TEACHER, self::ROLE_STUDENT]],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['learning_mode'], 'in', 'range' => [self::MODE_SELF_STUDY, self::MODE_TUTORED]],
            [['is_self_registered'], 'boolean'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'                  => 'ID',
            'role'                => 'Роль',
            'name'                => 'Имя',
            'email'               => 'Email',
            'username'            => 'Логин',
            'status'              => 'Статус',
            'telegram_chat_id'    => 'Telegram Chat ID',
            'vk_id'               => 'VK ID',
            'is_self_registered'  => 'Самостоятельная регистрация',
            'created_at'          => 'Дата регистрации',
        ];
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::class,
            ],
        ];
    }

    // ==================
    // IdentityInterface
    // ==================

    public static function findIdentity($id): ?self
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null; // JWT не используем в MVP
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    // ==================
    // Пароль
    // ==================

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken(): void
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
    }

    public static function findByPasswordResetToken(string $token): ?self
    {
        $expire    = 3600; // 1 час
        $parts     = explode('_', $token);
        $timestamp = (int) end($parts);

        if ($timestamp + $expire < time()) {
            return null;
        }

        return static::findOne(['password_reset_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    // ==================
    // Роли (хелперы)
    // ==================

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isAdminOrTeacher(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_TEACHER]);
    }

    public function isSelfStudy(): bool
    {
        return $this->learning_mode === self::MODE_SELF_STUDY;
    }

    public function isTutored(): bool
    {
        return $this->learning_mode === self::MODE_TUTORED;
    }

    /**
     * Синхронизировать режим обучения с фактом привязки к учителю.
     * Вызывать при назначении/снятии учителя.
     */
    public function syncLearningMode(): void
    {
        $this->learning_mode = $this->getTeacher()->exists()
            ? self::MODE_TUTORED
            : self::MODE_SELF_STUDY;
        $this->save(false);
    }

    // ==================
    // Связи
    // ==================

    public function getNotifications(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Notification::class, ['user_id' => 'id'])
                    ->orderBy(['created_at' => SORT_DESC]);
    }

    public function getUnreadNotifications(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Notification::class, ['user_id' => 'id'])
                    ->where(['is_read' => false])
                    ->orderBy(['created_at' => SORT_DESC]);
    }

    public function getTeacher(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id'])
                    ->viaTable('teacher_student', ['student_id' => 'id']);
    }

    public function getStudents(): \yii\db\ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'student_id'])
                    ->viaTable('teacher_student', ['teacher_id' => 'id']);
    }

    public function getGroups(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Group::class, ['id' => 'group_id'])
                    ->viaTable('group_student', ['student_id' => 'id']);
    }

    public function getTotalPoints(): int
    {
        return (int) PointTransaction::find()
            ->where(['student_id' => $this->id])
            ->sum('points');
    }

    // ==================
    // Статические методы
    // ==================

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByEmail(string $email): ?self
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    public static function getLabels(): array
    {
        return [
            self::ROLE_ADMIN   => 'Администратор',
            self::ROLE_TEACHER => 'Учитель',
            self::ROLE_STUDENT => 'Ученик',
            self::STATUS_PENDING  => 'Ожидает активации',
            self::STATUS_ACTIVE   => 'Активен',
            self::STATUS_INACTIVE => 'Неактивен',
        ];
    }
}