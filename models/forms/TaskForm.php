<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use app\models\Task;
use app\models\TaskFile;

class TaskForm extends Model
{
    public $id                  = null;
    public $task_number         = null;
    public ?string $title       = null;
    public string  $content     = '';
    public string  $answer      = '';
    public ?string $solution_content   = null;
    public $solution_is_public  = false;
    public $difficulty          = 5;
    public string  $status      = Task::STATUS_DRAFT;
    public array   $tag_ids     = [];
    public array $book_page_ids = [];
    public $seo_title = '';
    public $seo_description = '';
    public $answer_type = 'exact';

    private array $_files = [];

    private ?Task $_task = null;

    public function rules(): array
    {
        return [
            [['content'], 'required', 'message' => 'Обязательное поле'],
            [
                ['answer'],
                'required',
                'when' => fn($model) => $model->answer_type === 'exact',
                'message' => 'Укажите эталонный ответ, либо выберите тип "Развёрнутый".'
            ],
            [['answer_type'], 'in', 'range' => ['exact', 'manual']],
            [['answer_type'], 'default', 'value' => 'exact'],
            [['task_number'], 'integer', 'min' => 1, 'max' => 27],
            [['task_number'], 'default', 'value' => null],
            [['difficulty'], 'integer', 'min' => 1, 'max' => 10],
            [['content', 'solution_content'], 'string'],
            [['answer'], 'string', 'max' => 500],
            [['title'], 'string', 'max' => 255],
            [['solution_is_public'], 'boolean'],
            [['status'], 'in', 'range' => [Task::STATUS_DRAFT, Task::STATUS_PUBLISHED]],
            [['tag_ids', 'book_page_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'task_number'        => 'Номер задания',
            'title'              => 'Заголовок (необязательно)',
            'content'            => 'Условие задачи',
            'answer'             => 'Правильный ответ',
            'solution_content'   => 'Разбор решения',
            'solution_is_public' => 'Разбор виден публично',
            'difficulty'         => 'Сложность (1–10)',
            'status'             => 'Статус',
        ];
    }

    /**
     * Грузит файлы из $_FILES отдельно от load() — иначе Yii пытается
     * присвоить их как обычные атрибуты и падает с TypeError.
     */
    public function loadFiles(): void
    {
        $this->_files = UploadedFile::getInstancesByName('TaskForm[files]');
    }

    public static function fromTask(Task $task): self
    {
        $form                      = new self();
        $form->_task               = $task;
        $form->id                  = $task->id;
        $form->task_number = $task->task_number ?: null;
        $form->title               = $task->title;
        $form->content             = $task->content;
        $form->answer              = $task->answer;
        $form->solution_content    = $task->solution_content;
        $form->solution_is_public  = (bool) $task->solution_is_public;
        $form->difficulty          = $task->difficulty;
        $form->status              = $task->status;
        $form->tag_ids             = array_column($task->tags, 'id');
        $form->book_page_ids = array_column($task->bookPages, 'id');
        $form->seo_title = $task->seo_title ?? '';
        $form->seo_description = $task->seo_description ?? '';
        $form->answer_type = $task->answer_type ?? 'exact';

        return $form;
    }

    public function save(): ?Task
    {
        $this->loadFiles();

        if (!$this->validate()) {
            return null;
        }

        // Проверка лимита файлов (до 4 на задачу)
        $existingCount = $this->_task
            ? TaskFile::find()->where(['task_id' => $this->_task->id])->count()
            : 0;

        if ($existingCount + count($this->_files) > 4) {
            $this->addError('content', 'Можно прикрепить не более 4 файлов к задаче.');
            return null;
        }

        $task = $this->_task ?? new Task();

        $task->task_number = $this->task_number !== '' && $this->task_number !== null
            ? (int) $this->task_number
            : null;

        $task->difficulty  = (int) $this->difficulty;
        $task->title               = $this->title ?: null;
        $task->content             = $this->content;
        $task->answer_type = $this->answer_type;
        $task->answer       = $this->answer_type === Task::ANSWER_TYPE_MANUAL
            ? ''
            : trim($this->answer);
        $task->answer              = trim($this->answer);
        $task->solution_content    = $this->solution_content ?: null;
        $task->solution_is_public  = $this->solution_is_public;
        $task->status              = $this->status;
        $task->created_by          = Yii::$app->user->id;
        $task->seo_title       = trim($this->seo_title) ?: null;
        $task->seo_description = trim($this->seo_description) ?: null;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$task->save()) {
                throw new \RuntimeException(implode(', ', $task->getFirstErrors()));
            }

            // Теги
            Yii::$app->db->createCommand()
                ->delete('task_tag_pivot', ['task_id' => $task->id])->execute();

            foreach ($this->tag_ids as $tagId) {
                Yii::$app->db->createCommand()->insert('task_tag_pivot', [
                    'task_id' => $task->id,
                    'tag_id'  => (int) $tagId,
                ])->execute();
            }

            // Связанные страницы учебника
            Yii::$app->db->createCommand()
                ->delete('task_theory_link', ['task_id' => $task->id])->execute();

            foreach ($this->book_page_ids as $pageId) {
                Yii::$app->db->createCommand()->insert('task_theory_link', [
                    'task_id'      => $task->id,
                    'book_page_id' => (int) $pageId,
                ])->execute();
            }

            // Файлы (картинки и приложения — определяем тип по MIME)
            foreach ($this->_files as $uploadedFile) {
                $path = Yii::$app->storage->save($uploadedFile, 'tasks');

                $taskFile             = new TaskFile();
                $taskFile->task_id    = $task->id;
                $taskFile->filename   = $uploadedFile->name;
                $taskFile->path       = $path;
                $taskFile->mime_type  = $uploadedFile->type;
                $taskFile->size       = $uploadedFile->size;
                $taskFile->save();
            }

            if ($this->_files || $existingCount > 0) {
                $task->has_file = true;
                $task->save(false);
            }

            $transaction->commit();
            return $task;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            $this->addError('content', 'Ошибка сохранения: ' . $e->getMessage());
            return null;
        }
    }
}
