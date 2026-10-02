<?php

namespace app\controllers\student;

use Yii;
use app\models\HomeworkStudent;
use app\models\Lesson;
use app\models\PointTransaction;

class DashboardController extends BaseStudentController
{
    public function actionIndex()
    {
        $this->view->title = 'Дашборд';

        $student = $this->getStudent();
        $teacher = $student->getTeacher()->one();

        $activeHomework = HomeworkStudent::find()
            ->joinWith('homework')
            ->where(['homework_student.student_id' => $student->id])
            ->andWhere(['homework_student.status' => ['assigned', 'in_progress']])
            ->orderBy(['homework.deadline_at' => SORT_ASC])
            ->limit(5)
            ->all();

        $totalPoints = PointTransaction::getStudentTotal($student->id);

        $groupIds = \yii\helpers\ArrayHelper::getColumn($student->getGroups()->all(), 'id');

        $nextSession = \app\models\ClassSession::find()
            ->where([
                'or',
                ['student_id' => $student->id],
                ['in', 'group_id', $groupIds],
            ])
            ->andWhere(['status' => 'scheduled'])
            ->andWhere(['>=', 'scheduled_at', time()])
            ->orderBy('scheduled_at ASC')
            ->one();
        $heatmapWeeks = (new \app\services\ActivityHeatmapService())->buildForStudent($student->id);

        return $this->render('index', [
            'activeHomework' => $activeHomework,
            'totalPoints'    => $totalPoints,
            'teacher'        => $teacher,
            'nextSession'    => $nextSession,
            'heatmapWeeks' => $heatmapWeeks,
        ]);
    }
}
