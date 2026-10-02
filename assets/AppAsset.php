<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $sourcePath = null;
    public $basePath   = '@webroot';
    public $baseUrl    = '@web';

    public $css = [
        'css/app.css',
    ];

    public $js = [
        'js/htmx.min.js',
        'js/app.js',
    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_END,
    ];
}