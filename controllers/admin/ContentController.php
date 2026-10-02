<?php

namespace app\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\BookSection;
use app\models\BookChapter;
use app\models\BookPage;

class ContentController extends BaseAdminController
{
    // ==================
    // Разделы книги
    // ==================
    public function actionBookSections()
    {
        $this->view->title = 'Разделы учебника';

        $sections = BookSection::find()->orderBy('sort_order')->all();

        return $this->render('book-sections', ['sections' => $sections]);
    }

    public function actionCreateSection()
    {
        $this->view->title = 'Новый раздел';
        $error = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $section              = new BookSection();
            $section->title       = trim($data['title'] ?? '');
            $customSlug           = trim($data['slug'] ?? '');
            $section->slug        = $customSlug !== '' ? \yii\helpers\Inflector::slug($customSlug) : \yii\helpers\Inflector::slug($section->title);
            $section->description = trim($data['description'] ?? '') ?: null;
            $section->sort_order  = (int) ($data['sort_order'] ?? 0);

            if ($section->title === '') {
                $error = 'Введите название раздела.';
            } elseif (BookSection::find()->where(['slug' => $section->slug])->exists()) {
                $error = 'Такой URL уже занят, укажите другой.';
            } elseif ($section->save()) {
                Yii::$app->session->setFlash('success', 'Раздел создан.');
                return $this->redirect(['/admin/content/book-sections']);
            } else {
                $error = implode(', ', $section->getFirstErrors());
            }
        }

        return $this->render('section-form', ['section' => null, 'error' => $error, 'isNew' => true]);
    }

    public function actionUpdateSection(int $id)
    {
        $section = BookSection::findOne($id);
        if (!$section) throw new NotFoundHttpException('Раздел не найден.');

        $this->view->title = 'Раздел: ' . $section->title;
        $error = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $newTitle = trim($data['title'] ?? '');
            $newSlug  = \yii\helpers\Inflector::slug(trim($data['slug'] ?? ''));

            if ($newTitle === '') {
                $error = 'Введите название.';
            } elseif ($newSlug === '') {
                $error = 'URL не может быть пустым.';
            } elseif ($newSlug !== $section->slug && BookSection::find()->where(['slug' => $newSlug])->exists()) {
                $error = 'Такой URL уже занят, укажите другой.';
            } else {
                $section->title       = $newTitle;
                $section->slug        = $newSlug;
                $section->description = trim($data['description'] ?? '') ?: null;
                $section->sort_order  = (int) ($data['sort_order'] ?? 0);

                if ($section->save()) {
                    Yii::$app->session->setFlash('success', 'Раздел обновлён.');
                    return $this->redirect(['/admin/content/book-sections']);
                }
                $error = implode(', ', $section->getFirstErrors());
            }
        }

        return $this->render('section-form', ['section' => $section, 'error' => $error, 'isNew' => false]);
    }

    public function actionDeleteSection(int $id)
    {
        $section = BookSection::findOne($id);
        if (!$section) throw new NotFoundHttpException();

        $hasChapters = BookChapter::find()->where(['section_id' => $id])->exists();
        if ($hasChapters) {
            Yii::$app->response->statusCode = 422;
            return 'Нельзя удалить раздел с главами внутри.';
        }

        $section->delete();
        Yii::$app->response->statusCode = 200;
        return '';
    }

    // ==================
    // Книга — оглавление конкретного раздела
    // ==================
    public function actionBook(int $sectionId = null)
    {
        $sections = BookSection::find()->orderBy('sort_order')->all();

        if (!$sectionId && $sections) {
            $sectionId = $sections[0]->id;
        }

        $this->view->title = 'Учебник';

        $currentSection = $sectionId ? BookSection::findOne($sectionId) : null;

        $chapters = $currentSection
            ? BookChapter::find()
            ->where(['section_id' => $currentSection->id, 'parent_id' => null])
            ->orderBy('sort_order')
            ->with(['children.pages', 'pages'])
            ->all()
            : [];

        return $this->render('book', [
            'sections'       => $sections,
            'currentSection' => $currentSection,
            'chapters'       => $chapters,
        ]);
    }

    // ==================
    // Создание главы
    // ==================
    public function actionCreateChapter(int $sectionId = null)
    {
        $this->view->title = 'Новая глава';

        $sections       = BookSection::find()->orderBy('sort_order')->all();
        $parentChapters = BookChapter::find()
            ->where(['parent_id' => null])
            ->andFilterWhere(['section_id' => $sectionId])
            ->orderBy('sort_order')
            ->all();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $chapter              = new BookChapter();
            $chapter->title       = trim($data['title'] ?? '');
            $chapter->slug        = \yii\helpers\Inflector::slug($chapter->title);
            $chapter->section_id  = (int) ($data['section_id'] ?? 0);
            $chapter->parent_id   = $data['parent_id'] ?: null;
            $chapter->sort_order  = (int) ($data['sort_order'] ?? 0);

            if ($chapter->title === '') {
                $error = 'Введите название главы.';
            } elseif (!$chapter->section_id) {
                $error = 'Выберите раздел.';
            } elseif ($chapter->save()) {
                Yii::$app->session->setFlash('success', 'Глава создана.');
                return $this->redirect(['/admin/content/book', 'sectionId' => $chapter->section_id]);
            } else {
                $error = implode(', ', $chapter->getFirstErrors());
            }
        }

        return $this->render('create-chapter', [
            'chapter'        => null,
            'sections'       => $sections,
            'sectionId'      => $sectionId,
            'parentChapters' => $parentChapters,
            'error'          => $error,
        ]);
    }

public function actionUpdateChapter(int $id)
{
    $chapter = $this->findChapter($id);
    $this->view->title = 'Глава: ' . $chapter->title;

    $sections       = BookSection::find()->orderBy('sort_order')->all();
    $parentChapters = BookChapter::find()
        ->where(['parent_id' => null, 'section_id' => $chapter->section_id])
        ->andWhere(['!=', 'id', $id])
        ->orderBy('sort_order')
        ->all();

    $error = null;

    if (Yii::$app->request->isPost) {
        $data = Yii::$app->request->post();

        $chapter->title      = trim($data['title'] ?? '');
        $chapter->section_id = (int) ($data['section_id'] ?? $chapter->section_id);
        $chapter->parent_id  = $data['parent_id'] ?: null;
        $chapter->sort_order = (int) ($data['sort_order'] ?? 0);

        if ($chapter->title === '') {
            $error = 'Введите название.';
        } else {
            $manualSlug = trim($data['slug'] ?? '');

            if ($manualSlug !== '' && $manualSlug !== $chapter->slug) {
                $newSlug = \yii\helpers\Inflector::slug($manualSlug);
                if (BookChapter::find()->where(['slug' => $newSlug])->andWhere(['!=', 'id', $chapter->id])->exists()) {
                    $error = 'Такой URL уже занят другой темой.';
                } else {
                    $chapter->slug = $newSlug;
                }
            }

            if ($error === null) {
                if ($chapter->save()) {
                    Yii::$app->session->setFlash('success', 'Глава обновлена.');
                    return $this->redirect(['/admin/content/book', 'sectionId' => $chapter->section_id]);
                }
                $error = implode(', ', $chapter->getFirstErrors());
            }
        }
    }

    return $this->render('create-chapter', [
        'chapter'        => $chapter,
        'sections'       => $sections,
        'sectionId'      => $chapter->section_id,
        'parentChapters' => $parentChapters,
        'error'          => $error,
    ]);
}

    public function actionDeleteChapter(int $id)
    {
        $chapter = $this->findChapter($id);

        $hasContent = BookPage::find()->where(['chapter_id' => $id])->exists()
            || BookChapter::find()->where(['parent_id' => $id])->exists();

        if ($hasContent) {
            Yii::$app->response->statusCode = 422;
            return 'Нельзя удалить главу с содержимым.';
        }

        $chapter->delete();
        Yii::$app->response->statusCode = 200;
        return '';
    }

    // ==================
    // Страницы
    // ==================
    public function actionCreatePage(?int $chapterId = null)
    {
        $this->view->title = 'Новая страница';

        $chapters = BookChapter::find()
            ->orderBy(['section_id' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->with('section')
            ->all();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data  = Yii::$app->request->post();
            $error = $this->savePage(null, $data);

            if ($error === null) {
                Yii::$app->session->setFlash('success', 'Страница создана.');
                $chapter = BookChapter::findOne((int) $data['chapter_id']);
                return $this->redirect(['/admin/content/book', 'sectionId' => $chapter->section_id ?? null]);
            }
        }

        return $this->render('page-form', [
            'page'      => null,
            'chapters'  => $chapters,
            'chapterId' => $chapterId,
            'error'     => $error,
            'isNew'     => true,
        ]);
    }

    public function actionUpdatePage(int $id)
    {
        $page = $this->findPage($id);
        $this->view->title = $page->title;

        $chapters = BookChapter::find()
            ->orderBy(['section_id' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->with('section')
            ->all();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data  = Yii::$app->request->post();
            $error = $this->savePage($page, $data);

            if ($error === null) {
                Yii::$app->session->setFlash('success', 'Страница сохранена.');
                return $this->redirect(['/admin/content/update-page', 'id' => $page->id]);
            }
        }

        return $this->render('page-form', [
            'page'      => $page,
            'chapters'  => $chapters,
            'chapterId' => $page->chapter_id,
            'error'     => $error,
            'isNew'     => false,
        ]);
    }

    public function actionDeletePage(int $id)
    {
        $page = $this->findPage($id);
        $page->delete();

        Yii::$app->response->statusCode = 200;
        return '';
    }

    public function actionTogglePageStatus(int $id)
    {
        $page = $this->findPage($id);
        $page->published_at = $page->published_at ? null : time();
        $page->save(false);

        return $this->renderPartial('_page_status', ['page' => $page]);
    }

    public function actionRenderPreview()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $content = Yii::$app->request->post('content', '');
        return \app\helpers\ContentRenderer::render($content);
    }

    // ==================
    // Приватные хелперы
    // ==================
    private function savePage(?BookPage $page, array $data): ?string
    {
        $isNew = $page === null;
        if ($isNew) {
            $page = new BookPage();
        }

        $page->chapter_id      = (int) ($data['chapter_id'] ?? 0);
        $page->title           = trim($data['title'] ?? '');
        $page->content         = $data['content'] ?? '';
        $page->sort_order      = (int) ($data['sort_order'] ?? 0);
        $page->seo_title       = trim($data['seo_title'] ?? '') ?: null;
        $page->seo_description = trim($data['seo_description'] ?? '') ?: null;
        $page->published_at    = isset($data['published']) && $data['published']
            ? ($page->published_at ?? time())
            : null;

        if ($page->title === '') return 'Введите заголовок.';
        if ($page->chapter_id === 0) return 'Выберите главу.';

        $manualSlug = trim($data['slug'] ?? '');

        if ($isNew) {
            $baseSlug = $manualSlug !== '' ? \yii\helpers\Inflector::slug($manualSlug) : \yii\helpers\Inflector::slug($page->title);
            $slug     = $baseSlug;
            $i        = 1;
            while (BookPage::find()->where(['slug' => $slug])->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $page->slug = $slug;
        } elseif ($manualSlug !== '' && $manualSlug !== $page->slug) {
            $newSlug = \yii\helpers\Inflector::slug($manualSlug);
            if (BookPage::find()->where(['slug' => $newSlug])->andWhere(['!=', 'id', $page->id])->exists()) {
                return 'Такой URL уже занят другой страницей.';
            }
            $page->slug = $newSlug;
        }

        if (!$page->save()) {
            return implode(', ', $page->getFirstErrors());
        }

        return null;
    }

    private function findChapter(int $id): BookChapter
    {
        $chapter = BookChapter::findOne($id);
        if (!$chapter) throw new NotFoundHttpException('Глава не найдена.');
        return $chapter;
    }

    private function findPage(int $id): BookPage
    {
        $page = BookPage::findOne($id);
        if (!$page) throw new NotFoundHttpException('Страница не найдена.');
        return $page;
    }

    /**
     * Сохранить новый порядок страниц внутри главы (drag-and-drop)
     */
    public function actionReorderPages()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $pageIds = Yii::$app->request->post('page_ids', []);

        foreach ($pageIds as $order => $pageId) {
            Yii::$app->db->createCommand()
                ->update('book_page', ['sort_order' => $order], ['id' => (int) $pageId])
                ->execute();
        }

        return ['ok' => true];
    }
    public function actionToggleSectionStatus(int $id)
    {
        $section = BookSection::findOne($id);
        if (!$section) throw new NotFoundHttpException();

        $section->is_published = !$section->is_published;
        $section->save(false);

        return $this->renderPartial('_section_status', ['section' => $section]);
    }

    public function actionToggleChapterStatus(int $id)
    {
        $chapter = BookChapter::findOne($id);
        if (!$chapter) throw new NotFoundHttpException();

        $chapter->is_published = !$chapter->is_published;
        $chapter->save(false);

        return $this->renderPartial('_chapter_status', ['chapter' => $chapter]);
    }
}
