<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Инструменты';
?>

<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Инструменты</h1>
        <p class="text-base-400">Полезные калькуляторы и помощники для подготовки к ЕГЭ</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
        <a href="/tools/number-systems" class="card-interactive no-underline">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-3"
                style="background: rgba(79,70,229,0.1); color: #4F46E5;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7.5 3 12l6 4.5m6-9 6 4.5-6 4.5M14 4l-4 16" />
                </svg>
            </div>
            <h2 class="font-semibold text-base-100 mb-1">Перевод систем счисления</h2>
            <p class="text-sm text-base-400">2 ↔ 8 ↔ 10 ↔ 16 с подробным решением по шагам</p>
        </a>

        <a href="/python" class="card-interactive no-underline">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-3"
                style="background: rgba(34,197,94,0.1); color: #16A34A;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                </svg>
            </div>
            <h2 class="font-semibold text-base-100 mb-1">Питон-песочница</h2>
            <p class="text-sm text-base-400">Пишите и запускайте код Python прямо в браузере</p>
        </a>

        <a href="/tools/bits-bytes" class="card-interactive no-underline">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-3"
                style="background: rgba(14,165,233,0.1); color: #0EA5E9;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" />
                </svg>
            </div>
            <h2 class="font-semibold text-base-100 mb-1">Бит, байт, КБ, МБ, ГБ</h2>
            <p class="text-sm text-base-400">Перевод единиц измерения информации по шагам</p>
        </a>
        <a href="/tools/truth-table" class="card-interactive no-underline">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-3"
                style="background: rgba(225,29,72,0.1); color: #E11D48;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0111.25 18v-1.5m3.75-11.25V6a1.5 1.5 0 001.5 1.5h4.5m-6-3.75c0 .621-.504 1.125-1.125 1.125h-9M3.375 4.5c-.621 0-1.125.504-1.125 1.125"/>
                </svg>
            </div>
            <h2 class="font-semibold text-base-100 mb-1">Таблицы истинности</h2>
            <p class="text-sm text-base-400">Построение эталонной таблицы по выражению</p>
        </a>
        <a href="/tools/fano" class="card-interactive no-underline">
    <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-3"
         style="background: rgba(168,85,247,0.1); color: #A855F7;">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
        </svg>
    </div>
    <h2 class="font-semibold text-base-100 mb-1">Условие Фано</h2>
    <p class="text-sm text-base-400">Проверка кода и пошаговое декодирование</p>
</a>
<a href="/tools/graph-editor" class="card-interactive no-underline">
    <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-3"
         style="background: rgba(6,182,212,0.1); color: #06B6D4;">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 6.75a3 3 0 11-6 0 3 3 0 016 0zM9 20.25a3 3 0 106 0M4.5 12a3 3 0 106 0 3 3 0 00-6 0zm9 0a3 3 0 106 0 3 3 0 00-6 0zM7.5 15l3-3m3 0l3 3M12 9.75v4.5"/>
        </svg>
    </div>
    <h2 class="font-semibold text-base-100 mb-1">Редактор графов</h2>
    <p class="text-sm text-base-400">Построение, таблица смежности, расчёт маршрутов</p>
</a>
    </div>
</div>