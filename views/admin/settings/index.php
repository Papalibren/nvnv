<?php
/** @var app\models\SiteSetting $setting */
use yii\helpers\Html;

$this->title = 'Настройки сайта';
?>

<h1 class="text-2xl font-bold text-base-100 mb-6">Настройки сайта</h1>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid grid-cols-2 gap-6">

        <div class="space-y-6">

            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-4">Обратный отсчёт</h2>
                <div class="field-group mb-0">
                    <label>Дата ЕГЭ по информатике</label>
                    <input type="date" name="ege_date" class="input"
                           value="<?= $setting->ege_date ? date('Y-m-d', $setting->ege_date) : '' ?>">
                </div>
            </div>

            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-4">SEO по умолчанию</h2>

                <div class="field-group">
                    <label>Название сайта</label>
                    <input type="text" name="site_name" class="input"
                           value="<?= Html::encode($setting->site_name ?? '') ?>" placeholder="ЕГЭ Информатика">
                </div>

                <div class="field-group">
                    <label>Описание сайта по умолчанию</label>
                    <textarea name="default_meta_description" class="input" rows="3"><?= Html::encode($setting->default_meta_description ?? '') ?></textarea>
                    <p class="text-xs text-base-400 mt-1">Используется для страниц без собственного описания.</p>
                </div>

                <div class="field-group mb-0">
                    <label>Изображение для соцсетей (og:image) по умолчанию</label>
                    <?php if ($setting->default_og_image): ?>
                        <img src="/files/<?= $setting->default_og_image ?>" class="w-full rounded-lg mb-2" style="max-height:100px; object-fit:cover;">
                    <?php endif; ?>
                    <input type="file" name="default_og_image_file" accept="image/*"
                           class="text-xs text-base-400 file:btn-secondary file:mr-2 file:text-xs file:py-1">
                </div>
            </div>

        </div>

        <div class="space-y-6">

            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-4">Аналитика</h2>

                <div class="field-group">
                    <label>Яндекс.Метрика — номер счётчика</label>
                    <input type="text" name="yandex_metrika_id" class="input"
                           value="<?= Html::encode($setting->yandex_metrika_id ?? '') ?>" placeholder="12345678">
                </div>

                <div class="field-group mb-0">
                    <label>Google Analytics — ID измерения (G-XXXXXXX)</label>
                    <input type="text" name="google_analytics_id" class="input"
                           value="<?= Html::encode($setting->google_analytics_id ?? '') ?>" placeholder="G-XXXXXXXXXX">
                </div>
            </div>

            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-4">Подтверждение прав на сайт</h2>

                <div class="field-group">
                    <label>Google Search Console — код верификации</label>
                    <input type="text" name="google_search_console_code" class="input"
                           value="<?= Html::encode($setting->google_search_console_code ?? '') ?>"
                           placeholder="Содержимое meta-тега, без &lt;meta&gt;">
                </div>

                <div class="field-group mb-0">
                    <label>Яндекс.Вебмастер — код верификации</label>
                    <input type="text" name="yandex_webmaster_code" class="input"
                           value="<?= Html::encode($setting->yandex_webmaster_code ?? '') ?>">
                </div>
            </div>

            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-4">robots.txt — дополнительные правила</h2>
                <textarea name="robots_extra" class="input font-mono text-sm" rows="4"
                          placeholder="Disallow: /some-path/"><?= Html::encode($setting->robots_extra ?? '') ?></textarea>
            </div>

        </div>

    </div>

    <?= Html::submitButton('Сохранить настройки', ['class' => 'btn-primary mt-6 px-8 py-2.5']) ?>
</form>