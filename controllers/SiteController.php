<?php

namespace app\controllers;

use yii\web\Controller;
use Yii;
use yii\helpers\Html;

class SiteController extends Controller
{
    public $layout = '@app/views/layouts/main';

    public function actionError()
    {
        $exception = \Yii::$app->errorHandler->exception;
        return $this->render('error', ['exception' => $exception]);
    }

    public function actionIndex()
    {
        $this->view->title = 'ЕГЭ Информатика — подготовка к экзамену';

        $setting = \app\models\SiteSetting::current();

        return $this->render('index', [
            'daysUntilEge' => $setting->getDaysUntilEge(),
            'egeDate'      => $setting->ege_date,
        ]);
    }

    public function actionSitemap()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/xml; charset=utf-8');

        $baseUrl = Yii::$app->params['siteUrl'];
        $urls    = [];

        // Главная
        $urls[] = ['loc' => $baseUrl, 'priority' => '1.0'];

        // Задачи
        $tasks = \app\models\Task::find()->where(['status' => 'published'])->all();
        foreach ($tasks as $task) {
            $urls[] = ['loc' => $baseUrl . '/tasks/' . $task->id, 'priority' => '0.6'];
        }
        $urls[] = ['loc' => $baseUrl . '/tasks', 'priority' => '0.8'];

        // Книга
        $urls[] = ['loc' => $baseUrl . '/book', 'priority' => '0.7'];
        $sections = \app\models\BookSection::find()->all();
        foreach ($sections as $section) {
            $urls[] = ['loc' => $baseUrl . '/book/' . $section->slug, 'priority' => '0.6'];

            $pages = \app\models\BookPage::find()
                ->innerJoin('book_chapter', 'book_chapter.id = book_page.chapter_id')
                ->where(['book_chapter.section_id' => $section->id])
                ->andWhere(['not', ['book_page.published_at' => null]])
                ->all();

            foreach ($pages as $page) {
                $urls[] = ['loc' => $baseUrl . $page->getUrl(), 'priority' => '0.6'];
            }
        }

        // Лендинги (только индексируемые)
        $landings = \app\models\Landing::find()
            ->where(['status' => 'published', 'is_indexed' => true])
            ->all();
        foreach ($landings as $landing) {
            $urls[] = ['loc' => $baseUrl . '/' . $landing->slug, 'priority' => '0.9'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . Html::encode($url['loc']) . '</loc>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    public function actionRobots()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'text/plain; charset=utf-8');

        $baseUrl = Yii::$app->params['siteUrl'];
        $setting = \app\models\SiteSetting::current();

        $lines = [
            "User-agent: *",
            "Disallow: /admin/",
            "Disallow: /teacher/",
            "Disallow: /student/",
            "Disallow: /login",
            "Disallow: /register/",
            "Disallow: /password-reset",
        ];

        if ($setting->robots_extra) {
            $lines[] = trim($setting->robots_extra);
        }

        $lines[] = "";
        $lines[] = "Sitemap: {$baseUrl}/sitemap.xml";

        return implode("\n", $lines) . "\n";
    }
}
