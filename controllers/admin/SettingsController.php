<?php

namespace app\controllers\admin;

use Yii;
use app\models\SiteSetting;

class SettingsController extends BaseAdminController
{
public function actionIndex()
{
    $this->view->title = 'Настройки сайта';

    $setting = SiteSetting::current();

    if (Yii::$app->request->isPost) {
        $data = Yii::$app->request->post();

        $dateStr   = $data['ege_date'] ?? '';
        $timestamp = $dateStr ? strtotime($dateStr) : null;

        $setting->ege_date                     = $timestamp ?: null;
        $setting->site_name                    = trim($data['site_name'] ?? '') ?: null;
        $setting->default_meta_description     = trim($data['default_meta_description'] ?? '') ?: null;
        $setting->yandex_metrika_id             = trim($data['yandex_metrika_id'] ?? '') ?: null;
        $setting->google_analytics_id           = trim($data['google_analytics_id'] ?? '') ?: null;
        $setting->google_search_console_code    = trim($data['google_search_console_code'] ?? '') ?: null;
        $setting->yandex_webmaster_code         = trim($data['yandex_webmaster_code'] ?? '') ?: null;
        $setting->robots_extra                  = trim($data['robots_extra'] ?? '') ?: null;
        $setting->updated_at                    = time();

        $ogImage = \yii\web\UploadedFile::getInstanceByName('default_og_image_file');
        if ($ogImage) {
            $setting->default_og_image = Yii::$app->storage->save($ogImage, 'seo');
        }

        $setting->save(false);

        Yii::$app->session->setFlash('success', 'Настройки сохранены.');
        return $this->redirect(['/admin/settings/index']);
    }

    return $this->render('index', ['setting' => $setting]);
}
}