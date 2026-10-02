<?php
/** @var yii\web\View $this */
use yii\helpers\Html;

$this->title = 'Редактор графов — построение, таблица смежности, маршруты';
$this->registerMetaTag([
    'name'    => 'description',
    'content' => 'Онлайн-редактор графов: постройте граф мышью, получите таблицу смежности, кратчайшее расстояние и число маршрутов между вершинами. Экспорт в SVG и PNG.',
]);
$this->registerJsFile('@web/js/graph-editor.js', ['position' => \yii\web\View::POS_END]);
?>

<div class="max-w-6xl mx-auto px-4 py-10">

    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="/tools" class="hover:text-acid-lime no-underline">Инструменты</a>
        <span>/</span>
        <span class="text-base-100">Редактор графов</span>
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Редактор графов</h1>
        <p class="text-base-400">Постройте граф мышью — получите картинку, таблицу смежности и расчёт маршрутов.</p>
    </div>

    <!-- Панель режимов -->
    <div class="card mb-4">
        <div class="flex items-center gap-2 flex-wrap mb-2">
            <button type="button" class="graph-mode-btn" data-mode="vertex">Вершина</button>
            <button type="button" class="graph-mode-btn" data-mode="edge">Ребро</button>
            <button type="button" class="graph-mode-btn" data-mode="move">Перемещение</button>
            <button type="button" class="graph-mode-btn" data-mode="delete">Удаление</button>
            <span class="flex-1"></span>
            <button type="button" id="graph-btn-example" class="btn-secondary text-xs py-1.5 px-3">Пример</button>
            <button type="button" id="graph-btn-clear" class="btn-secondary text-xs py-1.5 px-3">Очистить</button>
        </div>
        <p id="graph-hint" class="text-xs text-base-400"></p>
    </div>

    <!-- Граф + сайдбар управления -->
    <div class="graph-layout mb-6">

        <div class="card graph-canvas-card">
            <svg id="graph-canvas" viewBox="0 0 900 640" preserveAspectRatio="xMidYMid meet"></svg>
        </div>

        <div class="graph-sidebar">

            <div class="card graph-sidebar-card">

                <div class="graph-sidebar-section">
                    <p class="graph-sidebar-label">Вершины</p>
                    <div id="graph-v-list" class="graph-list"></div>
                </div>

                <div class="graph-sidebar-section">
                    <p class="graph-sidebar-label">Рёбра</p>
                    <div id="graph-e-list" class="graph-list"></div>
                </div>

                <div class="graph-sidebar-section">
                    <p class="graph-sidebar-label">Настройки</p>
                    <div class="graph-opts">
                        <label>Радиус точки</label><input type="number" id="optRadius" class="input" value="4" min="1" max="10">
                        <label>Толщина линии</label><input type="number" id="optStroke" class="input" value="1.5" step="0.5" min="0.5" max="6">
                        <label>Размер шрифта</label><input type="number" id="optFont" class="input" value="16" min="8" max="30">
                        <label>Белый фон</label><input type="checkbox" id="optBg" checked>
                        <label>Обрезать по границам</label><input type="checkbox" id="optCrop" checked>
                    </div>
                </div>

                <div class="graph-sidebar-section" style="border-bottom: none; padding-bottom: 0; margin-bottom: 0;">
                    <p class="graph-sidebar-label">Экспорт</p>
                    <div class="flex gap-2">
                        <button type="button" id="graph-btn-download-svg" class="btn-secondary text-xs py-1.5 flex-1">.svg</button>
                        <button type="button" id="graph-btn-download-png" class="btn-primary text-xs py-1.5 flex-1">.png</button>
                    </div>
                </div>

            </div>

            <!-- Теория — под панелью управления -->
        <!--    <div class="card mt-4">
                <h2 class="text-base font-bold text-base-100 mb-3">Основные понятия</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="font-semibold text-base-100">Граф</p>
                        <p class="text-base-400 text-xs">Набор вершин и рёбер, соединяющих пары вершин.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-base-100">Степень вершины</p>
                        <p class="text-base-400 text-xs">Количество рёбер, выходящих из вершины.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-base-100">Таблица смежности</p>
                        <p class="text-base-400 text-xs">Матрица, где 1 на пересечении строки и столбца означает наличие ребра между вершинами.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-base-100">Путь (маршрут)</p>
                        <p class="text-base-400 text-xs">Последовательность рёбер, ведущая от одной вершины к другой без повторения вершин.</p>
                    </div>
                </div>
                <p class="text-xs text-base-400 mt-4">
                    Задания №3 и №8 ЕГЭ часто требуют построить таблицу смежности по графу или посчитать число маршрутов между городами/вершинами.
                </p>
            </div> -->

        </div>
    </div>

    <!-- Таблица смежности -->
    <div class="card mb-6 overflow-x-auto">
        <p class="text-sm font-semibold text-base-100 mb-3">Таблица смежности</p>
        <div id="graph-matrix-box"></div>
    </div>

    <!-- Расстояния и маршруты -->
    <div class="card">
        <p class="text-sm font-semibold text-base-100 mb-3">Расстояние и маршруты между вершинами</p>
        <div class="flex gap-2 items-end mb-4 flex-wrap">
            <div>
                <label>Из вершины</label>
                <select id="graph-dist-from" class="input" style="width:120px;"></select>
            </div>
            <div>
                <label>В вершину</label>
                <select id="graph-dist-to" class="input" style="width:120px;"></select>
            </div>
            <button type="button" id="graph-dist-calc" class="btn-primary text-sm py-2 px-5">Рассчитать</button>
        </div>
        <div id="graph-dist-result"></div>
    </div>

    <textarea id="graph-svg-out" class="hidden" spellcheck="false"></textarea>

</div>