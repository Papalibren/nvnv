<?php

namespace app\controllers\admin;

class DashboardController extends BaseAdminController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}