<?php

namespace app\console\controllers;

use yii\console\Controller;
use app\services\ExamService;

class ExamController extends Controller
{
    public function actionExpireAttempts()
    {
        $count = (new ExamService())->expireOverdueAttempts();
        echo "Закрыто просроченных попыток: {$count}\n";
    }
}

//* * * * * cd /path/to/ege && php yii exam/expire-attempts >> /dev/null 2>&1