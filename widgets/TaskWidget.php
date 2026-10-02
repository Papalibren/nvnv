<?php

namespace app\widgets;

use yii\base\Widget;
use app\models\Task;

class TaskWidget extends Widget
{
    public Task $task;

    /** public | homework | exam | admin */
    public string $mode = 'public';

    /** true — показывать ссылку "Открыть отдельно" (используется в каталоге) */
    public bool $showOpenLink = false;

    public function run(): string
    {
        return $this->render('task', [
            'task'         => $this->task,
            'mode'         => $this->mode,
            'showAnswer'   => $this->mode === 'public',
            'showSolution' => $this->mode === 'public' && $this->task->solution_is_public,
            'showOpenLink' => $this->showOpenLink,
        ]);
    }
}