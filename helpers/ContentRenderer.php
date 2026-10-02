<?php

namespace app\helpers;

use ParsedownExtra;
use yii\helpers\Inflector;

class ContentRenderer
{
    private static ?ParsedownExtra $parser = null;

    private const CALLOUT_ICONS = [
        'example'    => 'lightbulb',
        'definition' => 'bookmark',
        'note'       => 'info-circle',
        'warning'    => 'alert-triangle',
    ];

    private const CALLOUT_DEFAULT_TITLES = [
        'example'    => 'Пример',
        'definition' => 'Определение',
        'note'       => 'Заметка',
        'warning'    => 'Важно',
    ];

    public static function render(string $content): string
    {
        return self::renderWithHeadings($content)['html'];
    }

    /**
     * Возвращает html + список заголовков (для sidebar TOC страниц книги)
     */
    public static function renderWithHeadings(string $content): array
    {
        self::initParser();

        $content = str_replace(["\r\n", "\r"], "\n", $content);

        [$content, $callouts] = self::extractCallouts($content);

        $html = self::$parser->text($content);

        // python-static — то же самое подсвечивается как python, но без кнопки запуска
        $html = preg_replace(
            '/<pre><code class="language-python-static">/',
            '<pre class="language-python no-run"><code class="language-python">',
            $html
        );

        $html = preg_replace_callback(
            '/<pre><code class="language-(\w+)">/',
            fn($m) => '<pre class="language-' . $m[1] . '"><code class="language-' . $m[1] . '">',
            $html
        );

        [$html, $headings] = self::addHeadingAnchors($html);

        if ($headings) {
            $tocHtml = self::renderToc($headings);
            $html = preg_replace('/<p>\[\[toc\]\]<\/p>/', $tocHtml, $html);
        }

        foreach ($callouts as $token => $calloutHtml) {
            $html = str_replace("<p>{$token}</p>", $calloutHtml, $html);
            $html = str_replace($token, $calloutHtml, $html);
        }

        return ['html' => $html, 'headings' => $headings];
    }

    private static function initParser(): void
    {
        if (self::$parser === null) {
            self::$parser = new ParsedownExtra();
            self::$parser->setSafeMode(true);
        }
    }


    private static function extractCallouts(string $content): array
    {
        $callouts = [];
        $index = 0;

        // \R матчит любой перенос строки, но после нормализации выше это уже не критично —
        // оставляем как дополнительную защиту.
        $pattern = '/^:::(\w+)[ \t]*([^\n]*)\n(.*?)\n:::[ \t]*$/ms';

        $content = preg_replace_callback($pattern, function ($m) use (&$callouts, &$index) {
            $type        = strtolower($m[1]);
            $customTitle = trim($m[2] ?? '');
            $innerMd     = $m[3];

            if (!isset(self::CALLOUT_ICONS[$type])) {
                return $m[0];
            }

            $innerHtml = self::$parser->text($innerMd);
            $icon      = Icons::get(self::CALLOUT_ICONS[$type], 'w-5 h-5');
            $title     = $customTitle !== '' ? $customTitle : self::CALLOUT_DEFAULT_TITLES[$type];

            $html = '<div class="callout callout-' . $type . '">'
                . '<div class="callout-badge">' . $icon . '</div>'
                . '<p class="callout-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</p>'
                . '<div class="callout-body">' . $innerHtml . '</div>'
                . '</div>';

            $token = "\x02CALLOUT{$index}\x02";
            $callouts[$token] = $html;
            $index++;

            return $token;
        }, $content);

        return [$content, $callouts];
    }

    private static function addHeadingAnchors(string $html): array
    {
        $headings = [];
        $usedSlugs = [];

        $html = preg_replace_callback('/<h([23])>(.*?)<\/h\1>/s', function ($m) use (&$headings, &$usedSlugs) {
            $level = (int) $m[1];
            $text  = trim(strip_tags($m[2]));

            $slug = Inflector::slug($text, '-', true) ?: 'section';
            $base = $slug;
            $i = 1;
            while (isset($usedSlugs[$slug])) {
                $slug = $base . '-' . $i++;
            }
            $usedSlugs[$slug] = true;

            $headings[] = ['level' => $level, 'text' => $text, 'slug' => $slug];

            return '<h' . $level . ' id="' . $slug . '">' . $m[2] . '</h' . $level . '>';
        }, $html);

        return [$html, $headings];
    }

    private static function renderToc(array $headings): string
    {
        $html = '<nav class="content-toc"><p class="content-toc-title">Содержание</p><ul>';

        foreach ($headings as $h) {
            $indentClass = $h['level'] === 3 ? ' content-toc-sub' : '';
            $html .= '<li class="' . trim($indentClass) . '">'
                . '<a href="#' . $h['slug'] . '">' . htmlspecialchars($h['text'], ENT_QUOTES, 'UTF-8') . '</a>'
                . '</li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }
}
