<?php

namespace app\services;

use Yii;
use app\models\User;
use app\models\InviteToken;
use app\models\forms\RegisterForm;

class InviteService
{
    /**
     * Создать инвайт-токен для ученика
     */
    public function create(int $adminId, string $studentName): InviteToken
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Создаём пользователя-заглушку
            $user               = new User();
            $user->role         = User::ROLE_STUDENT;
            $user->name         = $studentName;
            $user->status       = User::STATUS_PENDING;
            $user->generateAuthKey();
            $user->setPassword(Yii::$app->security->generateRandomString(16));

            if (!$user->save()) {
                throw new \RuntimeException('Не удалось создать пользователя: '
                    . implode(', ', $user->getFirstErrors()));
            }

            // Создаём токен
            $invite             = new InviteToken();
            $invite->token      = Yii::$app->security->generateRandomString(32);
            $invite->student_id = $user->id;
            $invite->created_by = $adminId;
            $invite->expires_at = time() + 7 * 24 * 3600; // 7 дней

            if (!$invite->save()) {
                throw new \RuntimeException('Не удалось создать токен');
            }

            $transaction->commit();
            return $invite;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Активировать аккаунт по токену
     */
    public function activate(string $token, RegisterForm $form): User
    {
        $invite = InviteToken::find()
            ->where(['token' => $token])
            ->andWhere(['>', 'expires_at', time()])
            ->andWhere(['used_at' => null])
            ->one();

        if (!$invite) {
            throw new \RuntimeException('Ссылка недействительна или истекла.');
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $user           = $invite->student;
            $user->username = $form->username;
            $user->email    = $form->email ?: null;
            $user->status   = User::STATUS_ACTIVE;
            $user->setPassword($form->password);

            if (!$user->save()) {
                throw new \RuntimeException('Ошибка сохранения: '
                    . implode(', ', $user->getFirstErrors()));
            }

            // Назначаем роль в RBAC
            $auth = Yii::$app->authManager;
            $role = $auth->getRole('student');
            if ($role && !$auth->getAssignment('student', $user->id)) {
                $auth->assign($role, $user->id);
            }

            // Помечаем инвайт использованным
            $invite->used_at = time();
            $invite->save();

            $transaction->commit();
            return $user;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Получить URL инвайта
     */
    public function getInviteUrl(InviteToken $invite): string
    {
        return Yii::$app->params['siteUrl'] . '/register/' . $invite->token;
    }
}