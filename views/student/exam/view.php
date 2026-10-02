<?php
/** @var app\models\Exam $exam */
/** @var app\models\ExamAttempt $attempt */
/** @var app\models\ExamAttemptAnswer[] $answers */
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\ContentRenderer;
use app\assets\KatexAsset;

KatexAsset::register($this);
$this->title = $exam->title;
?>

<?php if ($exam->is_proctored): ?>
<!-- Предупреждение о защищённом режиме -->
<div id="proctor-warning" class="card mb-6" style="border: 2px solid #7C3AED;">
    <div class="flex items-start gap-3">
        <span class="text-2xl">🔒</span>
        <div>
            <h2 class="font-semibold text-base-100 mb-1">Защищённый режим</h2>
            <p class="text-sm text-base-400 mb-3">
                Этот экзамен даёт баллы в публичный рейтинг. Требуется полноэкранный режим.
                Выход из полноэкранного режима и переключение вкладок фиксируются.
            </p>
            <button onclick="enterFullscreenAndStart()" class="btn-primary">
                Войти в полноэкранный режим и начать
            </button>
        </div>
    </div>
</div>
<div id="exam-content" class="hidden">
<?php else: ?>
<div id="exam-content">
<?php endif; ?>

<!-- Таймер -->
<?php if ($exam->hasTimer()): ?>
<div class="card mb-6 text-center" style="border: 1px solid #E2E8F0;">
    <p class="text-xs text-base-400 mb-1">Осталось времени</p>
    <p id="timer-display" class="text-3xl font-bold text-base-100 font-mono">--:--:--</p>
</div>
<?php endif; ?>

<div class="space-y-4">
    <?php foreach ($exam->examTasks as $i => $et): ?>
        <?php $answer = $answers[$et->task_id] ?? null; ?>
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <span class="badge-indigo">Задача <?= $i + 1 ?></span>
            </div>

            <div class="prose-task text-sm mb-4">
                <?= ContentRenderer::render($et->task->content) ?>
            </div>

            <input type="text"
                   class="input font-mono"
                   placeholder="Введите ответ..."
                   value="<?= $answer ? Html::encode($answer->student_answer) : '' ?>"
                   hx-post="<?= Url::to(['/student/exam/answer', 'id' => $attempt->id]) ?>"
                   hx-trigger="keyup changed delay:600ms"
                   hx-vals='{"task_id": <?= $et->task_id ?>}'
                   hx-swap="none"
                   name="answer">
        </div>
    <?php endforeach; ?>
</div>

<div class="mt-8 flex justify-end">
    <form method="post" action="<?= Url::to(['/student/exam/submit', 'id' => $attempt->id]) ?>">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <?= Html::submitButton('Завершить экзамен', [
            'class'   => 'btn-primary px-8 py-3',
            'onclick' => 'return confirm("Завершить экзамен? Это действие нельзя отменить.")',
        ]) ?>
    </form>
</div>

</div><!-- /exam-content -->

<script>
const attemptId   = <?= $attempt->id ?>;
const isProctored = <?= $exam->is_proctored ? 'true' : 'false' ?>;
const hasTimer    = <?= $exam->hasTimer() ? 'true' : 'false' ?>;
let remainingSeconds = <?= $exam->hasTimer() ? $attempt->getRemainingSeconds() : 0 ?>;

function enterFullscreenAndStart() {
    document.documentElement.requestFullscreen().then(() => {
        document.getElementById('proctor-warning').classList.add('hidden');
        document.getElementById('exam-content').classList.remove('hidden');
    }).catch(() => {
        alert('Не удалось войти в полноэкранный режим. Разрешите его в браузере.');
    });
}

// Детект выхода из fullscreen
if (isProctored) {
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            sendHeartbeat('fullscreen_exit');
        }
    });

    // Детект потери фокуса окна (переключение вкладки)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            sendHeartbeat('focus_lost');
        }
    });
}

function sendHeartbeat(event) {
    fetch('<?= Url::to(['/student/exam/heartbeat', 'id' => $attempt->id]) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: 'event=' + event,
    })
    .then(r => r.json())
    .then(data => {
        if (data.expired) {
            window.location.href = '<?= Url::to(['/student/exam/submit', 'id' => $attempt->id]) ?>';
        }
    });
}

// Таймер обратного отсчёта
if (hasTimer) {
    function formatTime(sec) {
        const h = Math.floor(sec / 3600);
        const m = Math.floor((sec % 3600) / 60);
        const s = sec % 60;
        return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
    }

    function updateTimer() {
        const display = document.getElementById('timer-display');
        if (display) display.textContent = formatTime(remainingSeconds);

        if (remainingSeconds <= 0) {
            window.location.href = '<?= Url::to(['/student/exam/submit', 'id' => $attempt->id]) ?>';
            return;
        }

        remainingSeconds--;
    }

    updateTimer();
    setInterval(updateTimer, 1000);

    // Синхронизация с сервером каждые 10 секунд
    setInterval(() => sendHeartbeat('sync'), 10000);
}
</script>