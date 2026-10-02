<?php
return [
    'cookieValidationKey' => 'e2e6c4234b5db5b2848a6e05a41f1f73',

    // Email администратора — куда приходят заявки с лендингов
    'adminEmail' => 'your@email.com',

    // Название сайта
    'siteName'   => 'ЕГЭ Информатика',

    // Базовый URL (без слеша в конце)
    'siteUrl'    => 'http://localhost:8080',

    // Настройки баллов
    'points' => [
        'lateCoefficient'  => 0.5,   // после дедлайна
        'retryCoefficient' => 0.8,   // вторая попытка
    ],

    'features' => [
    'courses'          => false,  // самостоятельное прохождение курса
    'rating'           => false,  // публичный рейтинг
    'publicChallenges' => false,  // публичные задачи недели
],
];