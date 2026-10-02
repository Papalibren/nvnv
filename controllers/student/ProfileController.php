<?php

namespace app\controllers\student;

use Yii;
use app\models\User;

class ProfileController extends BaseStudentController
{
    public function actionIndex()
    {
        $this->view->title = 'Профиль';

        $student = $this->getStudent();
        $error   = null;
        $success = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $student->display_name = trim($data['display_name'] ?? '') ?: null;
            $newUsername = trim($data['username'] ?? '');
            $newEmail    = trim($data['email'] ?? '');
            $newPassword = $data['new_password'] ?? '';

            if ($newUsername !== '' && $newUsername !== $student->username) {
                if (User::find()->where(['username' => $newUsername])->andWhere(['!=', 'id', $student->id])->exists()) {
                    $error = 'Такой логин уже занят.';
                } else {
                    $student->username = $newUsername;
                }
            }

            if ($error === null) {
                $student->email = $newEmail ?: null;
            }

            if ($error === null && $newPassword !== '') {
                if (strlen($newPassword) < 6) {
                    $error = 'Пароль минимум 6 символов.';
                } else {
                    $student->setPassword($newPassword);
                }
            }

            if ($error === null) {
                if ($student->save()) {
                    $success = 'Профиль обновлён.';
                } else {
                    $error = implode(', ', $student->getFirstErrors());
                }
            }
        }

        return $this->render('index', [
            'student' => $student,
            'teacher' => $student->getTeacher()->one(),
            'error'   => $error,
            'success' => $success,
        ]);
    }
}