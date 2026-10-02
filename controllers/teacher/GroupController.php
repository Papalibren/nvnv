<?php

namespace app\controllers\teacher;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\Group;
use app\models\User;

class GroupController extends BaseTeacherController
{
    public function actionIndex()
    {
        $this->view->title = 'Группы';

        $groups = Group::find()
            ->where(['teacher_id' => $this->getTeacher()->id])
            ->orderBy('created_at DESC')
            ->all();

        return $this->render('index', ['groups' => $groups]);
    }

    public function actionCreate()
    {
        $this->view->title = 'Новая группа';

        $error = null;

        if (Yii::$app->request->isPost) {
            $name = trim(Yii::$app->request->post('name', ''));

            if ($name === '') {
                $error = 'Введите название группы.';
            } else {
                $group             = new Group();
                $group->teacher_id = $this->getTeacher()->id;
                $group->name       = $name;
                $group->description = Yii::$app->request->post('description', '');

                if ($group->save()) {
                    Yii::$app->session->setFlash('success', 'Группа создана.');
                    return $this->redirect(['/teacher/group/view', 'id' => $group->id]);
                }

                $error = implode(', ', $group->getFirstErrors());
            }
        }

        return $this->render('create', ['error' => $error]);
    }

    public function actionView(int $id)
    {
        $group = $this->findGroup($id);
        $this->view->title = $group->name;

        $teacher = $this->getTeacher();
        $inGroup = array_column($group->students, 'id');

        // Ученики привязанные к этому учителю через teacher_student
        // без joinWith чтобы избежать конфликта алиасов таблицы user
        $available = User::find()
            ->innerJoin('teacher_student ts', 'ts.student_id = user.id')
            ->where(['ts.teacher_id' => $teacher->id])
            ->andWhere(['user.status' => User::STATUS_ACTIVE])
            ->andWhere(['user.role'   => User::ROLE_STUDENT])
            ->all();

        return $this->render('view', [
            'group'     => $group,
            'available' => $available,
            'inGroup'   => $inGroup,
        ]);
    }

    public function actionAddStudent(int $id)
    {
        $group      = $this->findGroup($id);
        $studentId  = (int) Yii::$app->request->post('student_id');

        $exists = Yii::$app->db->createCommand(
            'SELECT COUNT(*) FROM group_student WHERE group_id=:g AND student_id=:s',
            [':g' => $group->id, ':s' => $studentId]
        )->queryScalar();

        if (!$exists) {
            Yii::$app->db->createCommand()->insert('group_student', [
                'group_id'   => $group->id,
                'student_id' => $studentId,
                'joined_at'  => time(),
            ])->execute();
        }

        return $this->redirect(['/teacher/group/view', 'id' => $group->id]);
    }

    public function actionRemoveStudent(int $id)
    {
        $group     = $this->findGroup($id);
        $studentId = (int) Yii::$app->request->post('student_id');

        Yii::$app->db->createCommand()
            ->delete('group_student', ['group_id' => $group->id, 'student_id' => $studentId])
            ->execute();

        return $this->redirect(['/teacher/group/view', 'id' => $group->id]);
    }

    private function findGroup(int $id): Group
    {
        $group = Group::findOne(['id' => $id, 'teacher_id' => $this->getTeacher()->id]);
        if (!$group) {
            throw new NotFoundHttpException('Группа не найдена.');
        }
        return $group;
    }
    public function actionTimeline(int $id)
    {
        $group = $this->findGroup($id);
        $this->view->title = 'Таймлайн: ' . $group->name;

        $sessions = \app\models\ClassSession::find()
            ->where(['group_id' => $group->id])
            ->orderBy('scheduled_at DESC')
            ->all();

        return $this->render('timeline', ['group' => $group, 'sessions' => $sessions]);
    }
}
