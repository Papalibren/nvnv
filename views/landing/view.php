<?php
/** @var yii\web\View $this */
/** @var app\models\Landing $landing */
use yii\helpers\Html;
use yii\helpers\Url;
?>

<!-- Мини-хедер с логотипом (не полная навигация сайта) -->
<header class="py-4 px-4" style="border-bottom: 1px solid #E2E8F0;">
    <div class="max-w-5xl mx-auto">
        <a href="/" class="text-lg font-bold grad-text no-underline">
            ЕГЭ Информатика
        </a>
    </div>
</header>

<!-- Контент лендинга (сырой HTML из админки) -->
<main>
    <?= $landing->content ?>
</main>

<!-- Форма заявки — плавающая, встраивается на любой лендинг -->
<section id="lead-form-section" class="py-16 px-4" style="background: #F8FAFC;">
    <div style="max-width: 440px; margin: 0 auto;">
        <div class="card">
            <h2 class="text-xl font-bold text-base-100 mb-1 text-center">
                <?= Html::encode($landing->form_title) ?>
            </h2>
            <p class="text-sm text-base-400 text-center mb-6">
                Оставьте контакты — свяжемся в течение дня
            </p>

            <div id="lead-form-wrapper">
                <?= $this->render('_lead_form_fields', ['landingId' => $landing->id]) ?>
            </div>
        </div>
    </div>
</section>

<footer class="py-6 px-4 text-center text-sm text-base-400">
    © <?= date('Y') ?> ЕГЭ Информатика
</footer>

<script>
// Захватываем UTM-метки из URL при загрузке страницы
(function() {
    const params = new URLSearchParams(window.location.search);
    ['utm_source', 'utm_medium', 'utm_campaign'].forEach(key => {
        const value = params.get(key);
        if (value) {
            const input = document.getElementById(key);
            if (input) input.value = value;
        }
    });
})();

// После успешной отправки формы — показываем сообщение об успехе
document.addEventListener('htmx:afterRequest', function(e) {
    if (e.detail.elt.id !== 'lead-form') return;

    try {
        const response = JSON.parse(e.detail.xhr.responseText);
        const wrapper  = document.getElementById('lead-form-wrapper');

        if (response.ok) {
            wrapper.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-14 h-14 rounded-full bg-acid-lime/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-acid-lime" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </div>
                    <p class="text-base-100 font-medium">${response.message}</p>
                </div>
            `;
        } else {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert-error mb-4';
            errorDiv.textContent = response.message;
            wrapper.insertBefore(errorDiv, wrapper.firstChild);
        }
    } catch (err) {
        console.error('Lead form response parse error', err);
    }
});
</script>