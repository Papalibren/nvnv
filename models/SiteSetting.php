<?php

namespace app\models;

class SiteSetting extends BaseModel
{
    public static function tableName(): string
    {
        return 'site_setting';
    }

    public static function current(): self
    {
        return self::findOne(1) ?? new self();
    }

    public function getDaysUntilEge(): ?int
    {
        if (!$this->ege_date) return null;

        $diff = $this->ege_date - time();
        if ($diff <= 0) return 0;

        return (int) ceil($diff / 86400);
    }


    public function getYandexMetrikaSnippet(): string
    {
        if (!$this->yandex_metrika_id) return '';

        $id = htmlspecialchars($this->yandex_metrika_id, ENT_QUOTES);
        return <<<HTML
    <script type="text/javascript">
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();
    for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
    k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
    ym({$id}, "init", {
            clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true
    });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/{$id}" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    HTML;
    }

    public function getGoogleAnalyticsSnippet(): string
    {
        if (!$this->google_analytics_id) return '';

        $id = htmlspecialchars($this->google_analytics_id, ENT_QUOTES);
        return <<<HTML
    <script async src="https://www.googletagmanager.com/gtag/js?id={$id}"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{$id}');
    </script>
    HTML;
    }
}