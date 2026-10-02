<?php

namespace app\assets;

use yii\web\AssetBundle;

class CodeRunnerAsset extends AssetBundle
{
    public $sourcePath = null;
    public $basePath   = '@webroot';
    public $baseUrl    = '@web';

    public $js = [
        'js/pyodide-runner.js',
        'js/code-runner-inject.js',

    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_END,
    ];
}