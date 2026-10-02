<?php

namespace app\console\controllers;

use yii\console\Controller;
use app\services\CourseService;

class CourseController extends Controller
{
    public function actionProcessUnlocks()
    {
        $count = (new CourseService())->processUnlocks();
        echo "Разблокировано тем: {$count}\n";
    }
}

//0 6 * * * cd /path/to/ege && php yii course/process-unlocks >> /dev/null 2>&1