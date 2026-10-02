<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\BookSection;
use app\models\BookChapter;
use app\models\BookPage;
use app\helpers\ContentRenderer;
use app\helpers\Seo;

class BookController extends Controller
{
    public $layout = '@app/views/layouts/main';

    public function actionIndex()
    {
        $sections = BookSection::find()->where(['is_published' => 1])->orderBy('sort_order')->all();

        Seo::set($this->view, [
            'title'       => 'Учебник по информатике для подготовки к ЕГЭ',
            'description' => 'Теория по информатике и Python с примерами, разборами задач и практикой для подготовки к ЕГЭ.',
            'url'         => Yii::$app->params['siteUrl'] . '/book',
        ]);

        return $this->render('index', ['sections' => $sections]);
    }

    public function actionSection(string $sectionSlug)
    {
        $section = BookSection::findOne(['slug' => $sectionSlug]);
        if (!$section) {
            throw new NotFoundHttpException('Раздел не найден.');
        }

        if (!$section->is_published) {
            throw new NotFoundHttpException('Раздел не найден.');
        }

        $chapters = BookChapter::find()
            ->where(['section_id' => $section->id, 'parent_id' => null, 'is_published' => 1])
            ->orderBy('sort_order')
            ->with(['children.pages', 'pages'])
            ->all();

        Seo::set($this->view, [
            'title'       => $section->title . ' — учебник по информатике',
            'description' => $section->description ?: ('Теория по теме «' . $section->title . '» для подготовки к ЕГЭ по информатике.'),
            'url'         => Yii::$app->params['siteUrl'] . '/book/' . $section->slug,
            'breadcrumbs' => [
                ['name' => 'Учебник', 'url' => Yii::$app->params['siteUrl'] . '/book'],
                ['name' => $section->title, 'url' => null],
            ],
        ]);

        return $this->render('section', ['section' => $section, 'chapters' => $chapters]);
    }

    public function actionView(string $sectionSlug, string $chapterSlug, string $slug)
    {
        $page = BookPage::find()
            ->where(['slug' => $slug])
            ->andWhere(['not', ['published_at' => null]])
            ->one();

        if (!$page) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        $chapter = $page->chapter;
        $section = $chapter->section ?? null;

        if (!$chapter || !$section) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if (!$section->is_published || !$chapter->is_published) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if ($section->slug !== $sectionSlug || $chapter->slug !== $chapterSlug) {
            return $this->redirect($page->getUrl(), 301);
        }

        // Сквозная навигация по всем страницам раздела, независимо от главы
        $orderedPages = $section->getOrderedPages();
        $currentIndex = null;

        foreach ($orderedPages as $i => $p) {
            if ($p->id === $page->id) {
                $currentIndex = $i;
                break;
            }
        }

        $prev = ($currentIndex !== null && $currentIndex > 0) ? $orderedPages[$currentIndex - 1] : null;
        $next = ($currentIndex !== null && $currentIndex < count($orderedPages) - 1) ? $orderedPages[$currentIndex + 1] : null;

        $rendered = ContentRenderer::renderWithHeadings($page->content);

        Seo::set($this->view, [
            'title'                => $page->getSeoTitle() ?: ($page->title . ' — ' . $chapter->title),
            'description'          => $page->getSeoDescription() ?: mb_substr(strip_tags($rendered['html']), 0, 400),
            'url'                  => Yii::$app->params['siteUrl'] . $page->getUrl(),
            'type'                 => 'article',
            'schemaType'           => 'LearningResource',
            'learningResourceType' => 'lesson',
            'breadcrumbs' => [
                ['name' => 'Учебник', 'url' => Yii::$app->params['siteUrl'] . '/book'],
                ['name' => $section->title, 'url' => Yii::$app->params['siteUrl'] . '/book/' . $section->slug],
                ['name' => $chapter->title, 'url' => null],
                ['name' => $page->title, 'url' => null],
            ],
        ]);

        return $this->render('view', [
            'section'  => $section,
            'chapter'  => $chapter,
            'page'     => $page,
            'prev'     => $prev,
            'next'     => $next,
            'rendered' => $rendered,
        ]);
    }
}
