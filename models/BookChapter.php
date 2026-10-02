<?php

namespace app\models;

class BookChapter extends BaseModel
{
    public static function tableName(): string
    {
        return 'book_chapter';
    }

    public function rules(): array
    {
        return [
            [['title', 'slug', 'section_id'], 'required'],
            [['title', 'slug'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            [['sort_order', 'parent_id', 'section_id'], 'integer'],
            [['is_published'], 'boolean'],
        ];
    }

    public function getSection(): \yii\db\ActiveQuery
    {
        return $this->hasOne(BookSection::class, ['id' => 'section_id']);
    }

    public function getPages(): \yii\db\ActiveQuery
    {
        return $this->hasMany(BookPage::class, ['chapter_id' => 'id'])->orderBy('sort_order');
    }

    public function getParent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(BookChapter::class, ['id' => 'parent_id']);
    }

    public function getChildren(): \yii\db\ActiveQuery
    {
        return $this->hasMany(BookChapter::class, ['parent_id' => 'id'])->orderBy('sort_order');
    }

    public function getPageCount(): int
    {
        $count = count($this->pages);
        foreach ($this->children as $child) {
            $count += count($child->pages);
        }
        return $count;
    }

    /**
     * Первая опубликованная страница темы — своя, а если своих нет,
     * то первая опубликованная страница первой подглавы.
     */
    public function getFirstPage(): ?BookPage
    {
        if (!$this->is_published) return null;

        $ownPage = BookPage::find()
            ->where(['chapter_id' => $this->id])
            ->andWhere(['not', ['published_at' => null]])
            ->orderBy('sort_order')
            ->one();

        if ($ownPage) return $ownPage;

        foreach ($this->children as $child) {
            $childPage = $child->getFirstPage();
            if ($childPage) return $childPage;
        }

        return null;
    }

    public function getLastPage(): ?BookPage
    {
        if (!$this->is_published) return null;

        foreach (array_reverse($this->children) as $child) {
            $childPage = $child->getLastPage();
            if ($childPage) return $childPage;
        }

        return BookPage::find()
            ->where(['chapter_id' => $this->id])
            ->andWhere(['not', ['published_at' => null]])
            ->orderBy('sort_order DESC')
            ->one();
    }

    /**
 * Опубликованные страницы этой главы, затем страницы всех подглав по порядку.
 */
public function getOrderedPages(): array
{
    if (!$this->is_published) {
        return [];
    }

    $pages = array_values(array_filter(
        $this->pages,
        fn($p) => $p->isPublished()
    ));

    foreach ($this->children as $child) {
        $pages = array_merge($pages, $child->getOrderedPages());
    }

    return $pages;
}
}
