<?php

namespace app\models;

class BookSection extends BaseModel
{
    public static function tableName(): string
    {
        return 'book_section';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['title', 'slug'], 'required'],
            [['title', 'slug'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 500],
            [['icon'], 'string', 'max' => 50],
            [['slug'], 'unique'],
            [['sort_order'], 'integer'],
            [['is_published'], 'boolean'],
        ];
    }

    public function getChapters(): \yii\db\ActiveQuery
    {
        return $this->hasMany(BookChapter::class, ['section_id' => 'id'])
                    ->where(['parent_id' => null])
                    ->orderBy('sort_order');
    }

    public function getPageCount(): int
    {
        return (int) BookPage::find()
            ->innerJoin('book_chapter', 'book_chapter.id = book_page.chapter_id')
            ->where(['book_chapter.section_id' => $this->id])
            ->andWhere(['not', ['book_page.published_at' => null]])
            ->count();
    }

    /**
     * Первая опубликованная страница раздела — берём первую тему по порядку,
     * у неё первую страницу (рекурсивно проверяя подтемы, если своих страниц нет).
     */
    public function getFirstPage(): ?BookPage
    {
        if (!$this->is_published) return null;

        $chapters = $this->getChapters()->where(['is_published' => 1])->with('children')->all();

        foreach ($chapters as $chapter) {
            $page = $chapter->getFirstPage();
            if ($page) return $page;
        }

        return null;
    }

    /**
 * Все опубликованные страницы раздела в порядке прохождения:
 * глава за главой (по sort_order), внутри главы — страницы, затем подглавы и их страницы.
 */
public function getOrderedPages(): array
{
    $pages = [];

    $chapters = $this->getChapters()
        ->where(['is_published' => 1])
        ->with(['pages', 'children.pages'])
        ->all();

    foreach ($chapters as $chapter) {
        $pages = array_merge($pages, $chapter->getOrderedPages());
    }

    return $pages;
}
}