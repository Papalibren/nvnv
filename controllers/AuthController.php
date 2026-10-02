<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\User;
use app\models\forms\LoginForm;
use app\models\forms\RegisterForm;
use app\models\forms\PasswordResetRequestForm;
use app\models\forms\ResetPasswordForm;
use app\services\InviteService;
use app\services\ActivityService;

class AuthController extends Controller
{
    public $layout = '@app/views/layouts/blank';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['login', 'register', 'signup'],
                'rules' => [
                    ['allow' => true, 'roles' => ['?']],
                ],
                'denyCallback' => function () {
                    return $this->redirectByRole();
                },
            ],
        ];
    }

    // ==================
    // Вход
    // ==================

    public function actionLogin()
    {
        $this->view->title = 'Вход';

        $form = new LoginForm();

        if ($form->load(Yii::$app->request->post()) && $form->login()) {
            ActivityService::log(
                Yii::$app->user->id,
                'login',
                null,
                null,
                ['username' => $form->username]
            );
            return $this->redirectByRole();
        }

        return $this->render('login', ['form' => $form]);
    }

    // ==================
    // Выход
    // ==================

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['/auth/login']);
    }

    // ==================
    // Регистрация по инвайту
    // ==================

    public function actionRegister(string $token)
    {
        $this->view->title = 'Активация аккаунта';

        // Проверяем токен
        $invite = \app\models\InviteToken::find()
            ->where(['token' => $token])
            ->andWhere(['>', 'expires_at', time()])
            ->andWhere(['used_at' => null])
            ->with('student')
            ->one();

        if (!$invite) {
            return $this->render('invite-invalid');
        }

        $form    = new RegisterForm();
        $service = new InviteService();
        $error   = null;

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $user = $service->activate($token, $form);
                Yii::$app->user->login($user, 3600 * 24 * 30);
                return $this->redirect(['/student/dashboard/index']);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('register', [
            'form'   => $form,
            'invite' => $invite,
            'error'  => $error,
        ]);
    }

    public function actionSignup()
{
    $this->view->title = 'Регистрация';

    $error = null;

    if (Yii::$app->request->isPost) {
        $data     = Yii::$app->request->post();
        $name     = trim($data['name'] ?? '');
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $email    = trim($data['email'] ?? '');

        if ($name === '' || $username === '' || strlen($password) < 6) {
            $error = 'Заполните имя, логин и пароль (минимум 6 символов).';
        } elseif (User::findByUsername($username)) {
            $error = 'Такой логин уже занят.';
        } elseif ($email !== '' && User::findByEmail($email)) {
            $error = 'Такой email уже используется.';
        } else {
            $user                     = new User();
            $user->role               = User::ROLE_STUDENT;
            $user->learning_mode      = User::MODE_SELF_STUDY;
            $user->name               = $name;
            $user->username           = $username;
            $user->email              = $email ?: null;
            $user->status             = User::STATUS_ACTIVE;
            $user->is_self_registered = true;
            $user->generateAuthKey();
            $user->setPassword($password);

            if ($user->save()) {
                $auth = Yii::$app->authManager;
                $auth->assign($auth->getRole('student'), $user->id);

                Yii::$app->user->login($user, 3600 * 24 * 30);
                return $this->redirect(['/student/dashboard/index']);
            }

            $error = implode(', ', $user->getFirstErrors());
        }
    }

    return $this->render('signup', ['error' => $error]);
}

    // ==================
    // Сброс пароля — запрос
    // ==================

    public function actionPasswordReset()
    {
        $this->view->title = 'Восстановление пароля';

        $form = new PasswordResetRequestForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $user = User::findByEmail($form->email);

            if ($user) {
                $user->generatePasswordResetToken();
                $user->save(false);

                $resetUrl = Yii::$app->params['siteUrl']
                    . '/password-reset/' . $user->password_reset_token;

                // Отправка email
                try {
                    Yii::$app->mailer->compose()
                        ->setTo($user->email)
                        ->setFrom([Yii::$app->params['adminEmail'] => 'ЕГЭ Информатика'])
                        ->setSubject('Восстановление пароля')
                        ->setTextBody("Ссылка для сброса пароля: {$resetUrl}\n\nСсылка действительна 1 час.")
                        ->send();
                } catch (\Exception $e) {
                    Yii::error('Ошибка отправки email: ' . $e->getMessage());
                }
            }

            // Всегда показываем успех (не раскрываем существование email)
            Yii::$app->session->setFlash('success',
                'Если такой email зарегистрирован — письмо отправлено.');
            return $this->refresh();
        }

        return $this->render('password-reset', ['form' => $form]);
    }

    // ==================
    // Сброс пароля — подтверждение
    // ==================

    public function actionPasswordResetConfirm(string $token)
    {
        $this->view->title = 'Новый пароль';

        $user = User::findByPasswordResetToken($token);

        if (!$user) {
            return $this->render('invite-invalid', [
                'message' => 'Ссылка для сброса пароля недействительна или истекла.',
            ]);
        }

        $form  = new ResetPasswordForm();
        $error = null;

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $user->setPassword($form->password);
            $user->removePasswordResetToken();

            if ($user->save(false)) {
                Yii::$app->session->setFlash('success', 'Пароль успешно изменён.');
                return $this->redirect(['/auth/login']);
            }

            $error = 'Не удалось сохранить пароль.';
        }

        return $this->render('password-reset-confirm', [
            'form'  => $form,
            'error' => $error,
        ]);
    }

    // ==================
    // Хелпер редиректа
    // ==================

    private function redirectByRole(): \yii\web\Response
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/auth/login']);
        }

        return match(Yii::$app->user->identity->role) {
            'admin'   => $this->redirect(['/admin/dashboard/index']),
            'teacher' => $this->redirect(['/teacher/dashboard/index']),
            default   => $this->redirect(['/student/dashboard/index']),
        };
    }
}