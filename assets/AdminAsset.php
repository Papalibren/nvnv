<?php

namespace app\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $sourcePath = null;
    public $basePath   = '@webroot';
    public $baseUrl    = '@web';

    public $js = [
        'js/admin.js',
    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_END,
    ];

    public $depends = [
        AppAsset::class,
    ];
}