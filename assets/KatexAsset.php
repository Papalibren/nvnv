<?php

namespace app\assets;

use yii\web\AssetBundle;

class KatexAsset extends AssetBundle
{
    public $sourcePath = null;
    public $basePath   = '@webroot';
    public $baseUrl    = '@web';

    public $css = [
        'katex/katex.min.css',
    ];

    public $js = [
        'katex/katex.min.js',
        'katex/contrib/auto-render.min.js',
    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_END,
    ];
}