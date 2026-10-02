<?php

namespace app\services;

use Yii;
use app\models\ActivityLog;

class ActivityService
{
    public static function log(
        int    $userId,
        string $action,
        string $entityType = null,
        int    $entityId   = null,
        array  $data       = []
    ): void {
        $log              = new ActivityLog();
        $log->user_id     = $userId;
        $log->action      = $action;
        $log->entity_type = $entityType;
        $log->entity_id   = $entityId;
        $log->data        = $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
        $log->ip          = Yii::$app->request->userIP;
        $log->save(false);
    }
}