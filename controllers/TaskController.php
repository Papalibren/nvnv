<?php

namespace app\controllers;

use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Task;
use app\models\TaskTag;
use Yii;
use app\helpers\Seo;

class TaskController extends Controller
{
    public $layout = '@app/views/layouts/main';

    public function actionIndex()
    {
        $this->view->title = 'Задачи ЕГЭ по информатике';

        $query = Task::find()
            ->where(['status' => Task::STATUS_PUBLISHED])
            ->orderBy(['task_number' => SORT_ASC, 'id' => SORT_DESC]);

        $number     = Yii::$app->request->get('number');
        $difficulty = Yii::$app->request->get('difficulty');
        $tag        = Yii::$app->request->get('tag');

        if ($number) {
            $query->andWhere(['task_number' => (int) $number]);
        }
        if ($difficulty) {
            $query->andWhere(['difficulty' => (int) $difficulty]);
        }
        if ($tag) {
            $query->innerJoin('task_tag_pivot', 'task_tag_pivot.task_id = task.id')
                ->innerJoin('task_tag', 'task_tag.id = task_tag_pivot.tag_id')
                ->andWhere(['task_tag.slug' => $tag]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 15],
        ]);

        // HTMX шлёт заголовок HX-Request — проверяем именно его
        if (Yii::$app->request->headers->has('HX-Request')) {
            return $this->renderPartial('_list', ['dataProvider' => $dataProvider]);
        }

        \app\helpers\Seo::set($this->view, [
            'title'       => 'Задачи ЕГЭ по информатике — все 27 заданий с разборами',
            'description' => 'Каталог задач для подготовки к ЕГЭ по информатике: все 27 типов заданий, фильтры по сложности и темам, разборы решений.',
            'url'         => Yii::$app->params['siteUrl'] . '/tasks',
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'allTags'      => TaskTag::find()->orderBy('name')->all(),
        ]);
    }

    public function actionView(int $id)
    {
        $task = Task::find()
            ->where(['id' => $id, 'status' => Task::STATUS_PUBLISHED])
            ->one();

        if (!$task) {
            throw new NotFoundHttpException('Задача не найдена.');
        }

        $this->view->title = 'Задание ' . $task->task_number . ' — ЕГЭ Информатика';

        $images = array_filter($task->files, fn($f) => $f->isImage());
        $firstImage = $images ? reset($images) : null;

        Seo::set($this->view, [
            'title'                => $task->getSeoTitle() ?: ('Задание ' . ($task->task_number ?: $task->id) . ' ЕГЭ по информатике — разбор и решение'),
            'description'          => $task->getSeoDescription() ?: strip_tags($task->content),
            'url'                  => Yii::$app->params['siteUrl'] . '/tasks/' . $task->id,
            'type'                 => 'article',
            'schemaType'           => 'LearningResource',
            'learningResourceType' => 'solution',
            'image'                => $firstImage ? Yii::$app->params['siteUrl'] . Yii::$app->storage->url($firstImage->path) : null,
            'breadcrumbs' => [
                ['name' => 'Задачи', 'url' => Yii::$app->params['siteUrl'] . '/tasks'],
                ['name' => 'Задание ' . ($task->task_number ?: $task->id), 'url' => null],
            ],
        ]);

        return $this->render('view', ['task' => $task]);
    }
}
