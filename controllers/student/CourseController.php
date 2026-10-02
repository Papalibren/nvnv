<?php

namespace app\controllers\student;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\Course;
use app\models\CourseEnrollment;
use app\models\CourseScheduleItem;
use app\models\StudentProgress;
use app\services\CourseService;

class CourseController extends BaseStudentController
{

public function beforeAction($action)
{
    if (empty(Yii::$app->params['features']['courses'])) {
        Yii::$app->session->setFlash('error', 'Раздел временно недоступен.');
        $this->redirect(['/student/dashboard/index']);
        return false;
    }
    return parent::beforeAction($action);
}


public function actionIndex()
    {
        $this->view->title = 'Мои курсы';

        $student = $this->getStudent();

        $enrollments = CourseEnrollment::find()
            ->where(['student_id' => $student->id])
            ->with('course')
            ->all();

        $enrolledCourseIds = array_column($enrollments, 'course_id');

        $availableCourses = Course::find()
            ->where(['status' => 'published'])
            ->andWhere(['not in', 'id', $enrolledCourseIds ?: [0]])
            ->all();

        return $this->render('index', [
            'enrollments'      => $enrollments,
            'availableCourses' => $availableCourses,
        ]);
    }

    public function actionView(int $courseId)
    {
        $student = $this->getStudent();
        $course  = Course::findOne($courseId);
        if (!$course) throw new NotFoundHttpException('Курс не найден.');

        $enrollment = CourseEnrollment::findOne([
            'course_id'  => $courseId,
            'student_id' => $student->id,
        ]);

        if (!$enrollment) {
            return $this->redirect(['/student/course/choose-duration', 'courseId' => $courseId]);
        }

        $this->view->title = $course->title;

        $items = CourseScheduleItem::find()
            ->where(['enrollment_id' => $enrollment->id])
            ->orderBy('unlock_at')
            ->with('courseLesson.lesson')
            ->all();

        return $this->render('roadmap', [
            'enrollment' => $enrollment,
            'items'      => $items,
        ]);
    }

    public function actionChooseDuration(int $courseId)
    {
        $course = Course::findOne($courseId);
        if (!$course) throw new NotFoundHttpException('Курс не найден.');

        $this->view->title = 'Начать курс';

        $student = $this->getStudent();
        $error   = null;

        if (Yii::$app->request->isPost) {
            $months = (int) Yii::$app->request->post('duration_months', 3);

            try {
                (new CourseService())->enroll($student->id, $months, $course->slug);
                return $this->redirect(['/student/course/view', 'courseId' => $course->id]);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('choose-duration', ['course' => $course, 'error' => $error]);
    }

    public function actionChangeDuration(int $enrollmentId)
    {
        $student    = $this->getStudent();
        $enrollment = CourseEnrollment::findOne(['id' => $enrollmentId, 'student_id' => $student->id]);

        if (!$enrollment) throw new NotFoundHttpException('Зачисление не найдено.');

        $error = null;

        if (Yii::$app->request->isPost) {
            $months = (int) Yii::$app->request->post('duration_months', $enrollment->duration_months);

            try {
                (new CourseService())->changeDuration($enrollment, $months);
                Yii::$app->session->setFlash('success', 'Срок обновлён, расписание пересчитано.');
                return $this->redirect(['/student/course/view', 'courseId' => $enrollment->course_id]);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('change-duration', ['enrollment' => $enrollment, 'error' => $error]);
    }
public function actionMarkRead(int $lessonId)
{
    $student = $this->getStudent();
    $lesson  = \app\models\Lesson::findOne($lessonId);

    if (!$lesson || !$lesson->isInfo()) {
        Yii::$app->response->statusCode = 404;
        return '';
    }

    if (!$lesson->isReadBy($student->id)) {
        $mark             = new \app\models\LessonReadMark();
        $mark->lesson_id  = $lessonId;
        $mark->student_id = $student->id;
        $mark->read_at    = time();
        $mark->save();

        // Пересчитываем прогресс всех курсов где встречается эта тема
        $courseLessons = \app\models\CourseLesson::find()->where(['lesson_id' => $lessonId])->all();
        foreach ($courseLessons as $cl) {
            (new CourseService())->recalcProgress($student->id, $cl->course_id);
        }
    }

    $enrollment = \app\models\CourseEnrollment::find()->where(['student_id' => $student->id])->all();
    // Определяем courseId для редиректа — берём из первого совпавшего course_lesson
    $cl = \app\models\CourseLesson::findOne(['lesson_id' => $lessonId]);

    return $this->redirect(['/student/course/view', 'courseId' => $cl->course_id ?? 0]);
}
}