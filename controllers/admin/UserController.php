<?php

namespace app\controllers\admin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\models\User;
use app\models\InviteToken;
use app\services\InviteService;

class UserController extends BaseAdminController
{
    // ==================
    // Список пользователей
    // ==================
    public function actionIndex()
    {
        $this->view->title = 'Пользователи';

        $role = Yii::$app->request->get('role', '');

        $query = User::find()->orderBy(['created_at' => SORT_DESC]);
        if ($role) {
            $query->andWhere(['role' => $role]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 30],
        ]);

        if (Yii::$app->request->headers->has('HX-Request')) {
            return $this->renderPartial('_list', ['dataProvider' => $dataProvider]);
        }

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    // ==================
    // Создание ученика + инвайт
    // ==================
    public function actionInviteStudent()
    {
        $this->view->title = 'Пригласить ученика';

        $name      = '';
        $inviteUrl = null;
        $error     = null;

        if (Yii::$app->request->isPost) {
            $name = trim(Yii::$app->request->post('name', ''));

            if ($name === '') {
                $error = 'Введите имя ученика.';
            } else {
                try {
                    $service   = new InviteService();
                    $invite    = $service->create(Yii::$app->user->id, $name);
                    $inviteUrl = $service->getInviteUrl($invite);

                    Yii::$app->session->setFlash('inviteUrl', $inviteUrl);
                    Yii::$app->session->setFlash('inviteName', $name);
                    return $this->redirect(['/admin/user/invite-student']);

                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        return $this->render('invite-student', [
            'name'      => $name,
            'error'     => $error,
            'inviteUrl' => Yii::$app->session->getFlash('inviteUrl'),
            'inviteName'=> Yii::$app->session->getFlash('inviteName'),
        ]);
    }

    // ==================
    // Создание учителя (просто и напрямую, без инвайта)
    // ==================
    public function actionCreateTeacher()
    {
        $this->view->title = 'Новый учитель';

        $error = null;

        if (Yii::$app->request->isPost) {
            $name     = trim(Yii::$app->request->post('name', ''));
            $username = trim(Yii::$app->request->post('username', ''));
            $password = Yii::$app->request->post('password', '');

            if ($name === '' || $username === '' || strlen($password) < 6) {
                $error = 'Заполните все поля. Пароль минимум 6 символов.';
            } elseif (User::findByUsername($username)) {
                $error = 'Такой логин уже занят.';
            } else {
                $user           = new User();
                $user->role     = User::ROLE_TEACHER;
                $user->name     = $name;
                $user->username = $username;
                $user->status   = User::STATUS_ACTIVE;
                $user->generateAuthKey();
                $user->setPassword($password);

                if ($user->save()) {
                    $auth = Yii::$app->authManager;
                    $auth->assign($auth->getRole('teacher'), $user->id);

                    Yii::$app->session->setFlash('success', 'Учитель создан.');
                    return $this->redirect(['/admin/user/index']);
                }

                $error = implode(', ', $user->getFirstErrors());
            }
        }

        return $this->render('create-teacher', ['error' => $error]);
    }

    // ==================
    // Привязка ученика к учителю
    // ==================
public function actionAssignTeacher(int $id)
{
    $student = $this->findUser($id);

    if ($student->role !== User::ROLE_STUDENT) {
        throw new NotFoundHttpException('Это не ученик.');
    }

    if (Yii::$app->request->isPost) {
        $teacherId = (int) Yii::$app->request->post('teacher_id');

        Yii::$app->db->createCommand()
            ->delete('teacher_student', ['student_id' => $student->id])
            ->execute();

        if ($teacherId > 0) {
            Yii::$app->db->createCommand()->insert('teacher_student', [
                'teacher_id' => $teacherId,
                'student_id' => $student->id,
            ])->execute();

            (new \app\services\NotificationService())->create(
                $teacherId,
                'student_assigned',
                'Вам назначен новый ученик',
                $student->name . ' теперь ваш ученик',
                'user',
                $student->id
            );
        }

        $student->syncLearningMode();

        Yii::$app->session->setFlash('success', 'Привязка обновлена.');
        return $this->redirect(['/admin/user/view', 'id' => $student->id]);
    }

    return $this->redirect(['/admin/user/view', 'id' => $student->id]);
}

    // ==================
    // Просмотр карточки пользователя
    // ==================
    public function actionView(int $id)
    {
        $user = $this->findUser($id);
        $this->view->title = $user->name;

        $teachers = User::find()->where(['role' => User::ROLE_TEACHER, 'status' => User::STATUS_ACTIVE])->all();


        $heatmapWeeks = $user->role === User::ROLE_STUDENT
    ? (new \app\services\ActivityHeatmapService())->buildForStudent($user->id)
    : [];

        return $this->render('view', [
            'user'     => $user,
            'teachers' => $teachers,
            'heatmapWeeks' => $heatmapWeeks,
        ]);
    }

    // ==================
    // Блокировка / разблокировка (HTMX)
    // ==================
    public function actionToggleStatus(int $id)
    {
        $user = $this->findUser($id);

        if ($user->status === User::STATUS_ACTIVE) {
            $user->status = User::STATUS_INACTIVE;
        } elseif ($user->status === User::STATUS_INACTIVE) {
            $user->status = User::STATUS_ACTIVE;
        }

        $user->save(false);

        return $this->renderPartial('_status_badge', ['user' => $user]);
    }

    // ==================
    // Хелпер
    // ==================
    private function findUser(int $id): User
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }
        return $user;
    }
}