<?php

namespace app\helpers;

use Yii;
use yii\web\View;
use app\models\SiteSetting;

class Seo
{
    /**
     * Полная установка SEO-тегов конкретной страницы.
     * После вызова помечает View флагом, чтобы ensureDefaults() из layout
     * не перезаписал уже установленные значения.
     */
    public static function set(View $view, array $options): void
    {
        $setting  = SiteSetting::current();
        $siteName = $setting->site_name ?: (Yii::$app->params['siteName'] ?? 'ЕГЭ Информатика');

        $title       = $options['title'] ?? $siteName;
        $description = $options['description'] ?? ($setting->default_meta_description ?: 'Подготовка к ЕГЭ по информатике: теория, задачи, пробные экзамены.');
        $description = self::truncateDescription($description, 160);

        $url     = $options['url'] ?? Yii::$app->request->absoluteUrl;
        $ogType  = $options['type'] ?? 'website';
        $image   = $options['image'] ?? ($setting->default_og_image ? Yii::$app->params['siteUrl'] . Yii::$app->storage->url($setting->default_og_image) : null);
        $noindex = $options['noindex'] ?? false;

        $view->title = $title;

        $view->registerMetaTag(['name' => 'description', 'content' => $description], 'description');

        $view->registerMetaTag(['property' => 'og:title', 'content' => $title], 'og:title');
        $view->registerMetaTag(['property' => 'og:description', 'content' => $description], 'og:description');
        $view->registerMetaTag(['property' => 'og:type', 'content' => $ogType], 'og:type');
        $view->registerMetaTag(['property' => 'og:url', 'content' => $url], 'og:url');
        $view->registerMetaTag(['property' => 'og:site_name', 'content' => $siteName], 'og:site_name');
        $view->registerMetaTag(['property' => 'og:locale', 'content' => 'ru_RU'], 'og:locale');

        $view->registerMetaTag(['name' => 'twitter:title', 'content' => $title], 'twitter:title');
        $view->registerMetaTag(['name' => 'twitter:description', 'content' => $description], 'twitter:description');

        if ($image) {
            $view->registerMetaTag(['property' => 'og:image', 'content' => $image], 'og:image');
            $view->registerMetaTag(['name' => 'twitter:card', 'content' => 'summary_large_image'], 'twitter:card');
            $view->registerMetaTag(['name' => 'twitter:image', 'content' => $image], 'twitter:image');
        } else {
            $view->registerMetaTag(['name' => 'twitter:card', 'content' => 'summary'], 'twitter:card');
        }

        $view->registerLinkTag(['rel' => 'canonical', 'href' => $url], 'canonical');

        if ($noindex) {
            $view->registerMetaTag(['name' => 'robots', 'content' => 'noindex, nofollow'], 'robots');
        }

        // ============ JSON-LD ============
        $graph = [];

        if (!empty($options['breadcrumbs'])) {
            $items = [];
            foreach ($options['breadcrumbs'] as $i => $bc) {
                $item = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $bc['name']];
                if (!empty($bc['url'])) $item['item'] = $bc['url'];
                $items[] = $item;
            }
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
        }

        if (!empty($options['schemaType'])) {
            $entry = [
                '@type'       => $options['schemaType'],
                'name'        => $title,
                'description' => $description,
                'url'         => $url,
                'inLanguage'  => 'ru',
            ];
            if ($options['schemaType'] === 'LearningResource') {
                $entry['learningResourceType'] = $options['learningResourceType'] ?? 'lesson';
                $entry['educationalLevel']     = 'secondary';
            }
            $graph[] = $entry;
        }

        if (!empty($graph)) {
            $view->params['jsonLd'][] = json_encode(
                ['@context' => 'https://schema.org', '@graph' => $graph],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        // Ключевая правка: помечаем что set() уже отработал —
        // ensureDefaults() из layout больше не должен ничего перезаписывать
        $view->params['_seoSet'] = true;
    }

    /**
     * Fallback для страниц, которые не вызвали set() явно.
     * Срабатывает ТОЛЬКО если set() ещё не был вызван на этой странице.
     */
    public static function ensureDefaults(View $view): void
    {
        if (!empty($view->params['_seoSet'])) {
            return;
        }

        $setting  = SiteSetting::current();

        $view->registerMetaTag([
            'name'    => 'description',
            'content' => $setting->default_meta_description ?: 'Подготовка к ЕГЭ по информатике: теория, задачи, пробные экзамены.',
        ], 'description');
    }

    /**
     * Обрезает текст до лимита, стараясь остановиться на границе предложения,
     * а не посреди слова.
     */
    public static function truncateDescription(string $text, int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit);

        // Ищем последнюю границу предложения в пределах обрезанного куска
        $lastSentenceEnd = max(
            mb_strrpos($cut, '.') ?: -1,
            mb_strrpos($cut, '!') ?: -1,
            mb_strrpos($cut, '?') ?: -1
        );

        if ($lastSentenceEnd !== -1 && $lastSentenceEnd > $limit * 0.5) {
            return mb_substr($cut, 0, $lastSentenceEnd + 1);
        }

        // Нет удобной границы предложения — обрезаем по последнему пробелу и ставим многоточие
        $lastSpace = mb_strrpos($cut, ' ');
        $cut = $lastSpace !== false ? mb_substr($cut, 0, $lastSpace) : $cut;

        return rtrim($cut, ' ,;:—-') . '…';
    }
}
