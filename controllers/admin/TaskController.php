<?php

namespace app\controllers\admin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\models\Task;
use app\models\TaskTag;
use app\models\forms\TaskForm;

class TaskController extends BaseAdminController
{
    // ==================
    // Список задач
    // ==================
public function actionIndex()
{
    $this->view->title = 'Задачи';

    $query = Task::find()->orderBy(['task_number' => SORT_ASC, 'id' => SORT_DESC]);

    $number     = Yii::$app->request->get('number');
    $status     = Yii::$app->request->get('status');
    $difficulty = Yii::$app->request->get('difficulty');

    if ($number)     $query->andWhere(['task_number' => (int) $number]);
    if ($status)     $query->andWhere(['status' => $status]);
    if ($difficulty) $query->andWhere(['difficulty' => (int) $difficulty]);

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
    // Создание задачи
    // ==================
    public function actionCreate()
    {
        $this->view->title = 'Новая задача';

        $form = new TaskForm();

        if (Yii::$app->request->isPost) {
            $form->load(Yii::$app->request->post());
            $task = $form->save();

            if ($task) {
                Yii::$app->session->setFlash('success', 'Задача создана.');
                return $this->redirect(['/admin/task/update', 'id' => $task->id]);
            }
        }

        return $this->render('form', [
            'form'    => $form,
            'tags'    => TaskTag::find()->orderBy('name')->all(),
            'bookPages' => \app\models\BookPage::find()
            ->with('chapter.section')
            ->orderBy(['chapter_id' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->all(),
            'isNew'   => true,
        ]);
    }

    // ==================
    // Редактирование
    // ==================
    public function actionUpdate(int $id)
    {
        $task = $this->findTask($id);
        $this->view->title = 'Задача #' . $task->id;

        $form = TaskForm::fromTask($task);

        if (Yii::$app->request->isPost) {
            $form->load(Yii::$app->request->post());
            $updated = $form->save();

            if ($updated) {
                Yii::$app->session->setFlash('success', 'Задача сохранена.');
                return $this->redirect(['/admin/task/update', 'id' => $updated->id]);
            }
        }

        return $this->render('form', [
            'form'    => $form,
            'tags'    => TaskTag::find()->orderBy('name')->all(),
            'bookPages' => \app\models\BookPage::find()
                ->with('chapter.section')
                ->orderBy(['chapter_id' => SORT_ASC, 'sort_order' => SORT_ASC])
                ->all(),
            'task'    => $task,
            'isNew'   => false,
        ]);
    }

    // ==================
    // Быстрая смена статуса (HTMX)
    // ==================
    public function actionToggleStatus(int $id)
    {
        $task = $this->findTask($id);
        $task->status = $task->status === Task::STATUS_PUBLISHED
            ? Task::STATUS_DRAFT
            : Task::STATUS_PUBLISHED;
        $task->save(false);

        return $this->renderPartial('_status_badge', ['task' => $task]);
    }

    // ==================
    // Удаление (HTMX)
    // ==================
    public function actionDelete(int $id)
    {
        $task = $this->findTask($id);

        $usages = [];

        $inHomework = (new \yii\db\Query())
            ->from('homework_task')->where(['task_id' => $id])->exists();
        if ($inHomework) $usages[] = 'используется в домашних заданиях';

        $inExam = (new \yii\db\Query())
            ->from('exam_task')->where(['task_id' => $id])->exists();
        if ($inExam) $usages[] = 'используется в экзаменах';

        $inChallenge = (new \yii\db\Query())
            ->from('public_challenge')->where(['task_id' => $id])->exists();
        if ($inChallenge) $usages[] = 'используется в публичных задачах недели';

        if (!empty($usages)) {
            Yii::$app->response->statusCode = 422;
            return 'Нельзя удалить задачу — она ' . implode(', ', $usages) . '. Сначала уберите её оттуда, либо снимите с публикации.';
        }

        $task->delete();

        Yii::$app->response->statusCode = 200;
        return '';
    }

// ==================
// Управление тегами
// ==================
public function actionTags()
{
    $this->view->title = 'Теги задач';

    $tags = TaskTag::find()->orderBy('name')->all();

    if (Yii::$app->request->isPost) {
        $name = trim(Yii::$app->request->post('name', ''));

        if ($name !== '') {
            $tag       = new TaskTag();
            $tag->name = $name;
            $tag->slug = \yii\helpers\Inflector::slug($name);

            if (!$tag->save()) {
                Yii::$app->session->setFlash('error',
                    implode(', ', $tag->getFirstErrors()));
            }
        }

        return $this->redirect(['/admin/task/tags']);
    }

    return $this->render('tags', ['tags' => $tags]);
}

// ==================
// Редактирование тега (HTMX, инлайн)
// ==================
public function actionTagUpdate(int $id)
{
    $tag = TaskTag::findOne($id);
    if (!$tag) {
        throw new NotFoundHttpException('Тег не найден.');
    }

    if (Yii::$app->request->isPost) {
        $name = trim(Yii::$app->request->post('name', ''));
        if ($name !== '') {
            $tag->name = $name;
            $tag->slug = \yii\helpers\Inflector::slug($name);
            $tag->save();
        }
    }

    return $this->renderPartial('_tag_row', ['tag' => $tag]);
}

// ==================
// Удаление тега (HTMX)
// ==================
public function actionTagDelete(int $id)
{
    $tag = TaskTag::findOne($id);
    if ($tag) {
        // Просто удаляем — связи в task_tag_pivot удалятся каскадом
        $tag->delete();
    }

    Yii::$app->response->statusCode = 200;
    return '';
}

    // ==================
    // Хелпер
    // ==================
    private function findTask(int $id): Task
    {
        $task = Task::findOne($id);
        if (!$task) {
            throw new NotFoundHttpException('Задача не найдена.');
        }
        return $task;
    }

    public function actionRenderPreview()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $content = Yii::$app->request->post('content', '');
        return \app\helpers\ContentRenderer::render($content);
    }
    public function actionDeleteFile(int $id)
    {
        $file = \app\models\TaskFile::findOne($id);
        if ($file) {
            Yii::$app->storage->delete($file->path);
            $file->delete();
        }

        Yii::$app->response->statusCode = 200;
        return '';
    }
}