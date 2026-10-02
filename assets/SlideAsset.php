<?php

namespace app\assets;

use yii\web\AssetBundle;

class SlideAsset extends AssetBundle
{
    public $sourcePath = null;
    public $basePath   = '@webroot';
    public $baseUrl    = '@web';

    public $css = [
        'css/slide.css',
    ];

    public $js = [
        'js/slide.js',
    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_END,
    ];

    public $depends = [
        KatexAsset::class,
        PrismAsset::class,
    ];
}