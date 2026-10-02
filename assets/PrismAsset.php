<?php

namespace app\assets;

use yii\web\AssetBundle;

class PrismAsset extends AssetBundle
{
    public $sourcePath = null;
    public $basePath   = '@webroot';
    public $baseUrl    = '@web';

    public $css = [
        'css/prism.css',
    ];

    public $js = [
        'js/prism.js',
        'js/prism-autoloader.js',
    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_END,
    ];
}