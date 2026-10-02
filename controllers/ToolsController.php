<?php

namespace app\controllers;

use yii\web\Controller;
use Yii;

class ToolsController extends Controller
{
    public $layout = '@app/views/layouts/main';

    public function actionIndex()
    {
        \app\helpers\Seo::set($this->view, [
            'title'       => 'Задачи ЕГЭ по информатике — все 27 заданий с разборами',
            'description' => 'Каталог задач для подготовки к ЕГЭ по информатике: все 27 типов заданий, фильтры по сложности и темам, разборы решений.',
            'url'         => Yii::$app->params['siteUrl'] . '/tasks',
        ]);
        $this->view->title = 'Инструменты';
        return $this->render('index');
    }

    public function actionNumberSystems()
    {
        \app\helpers\Seo::set($this->view, [
            'title'       => 'Задачи ЕГЭ по информатике — все 27 заданий с разборами',
            'description' => 'Каталог задач для подготовки к ЕГЭ по информатике: все 27 типов заданий, фильтры по сложности и темам, разборы решений.',
            'url'         => Yii::$app->params['siteUrl'] . '/tasks',
        ]);
        $this->view->title = 'Перевод систем счисления';
        return $this->render('number-systems');
    }

    public function actionBitsBytes()
    {

        \app\helpers\Seo::set($this->view, [
            'title'       => 'Задачи ЕГЭ по информатике — все 27 заданий с разборами',
            'description' => 'Каталог задач для подготовки к ЕГЭ по информатике: все 27 типов заданий, фильтры по сложности и темам, разборы решений.',
            'url'         => Yii::$app->params['siteUrl'] . '/tasks',
        ]);
        $this->view->title = 'Перевод бит, байт, КБ, МБ, ГБ';
        return $this->render('bits-bytes');
    }

    public function actionTruthTable()
    {
        \app\helpers\Seo::set($this->view, [
            'title'       => 'Задачи ЕГЭ по информатике — все 27 заданий с разборами',
            'description' => 'Каталог задач для подготовки к ЕГЭ по информатике: все 27 типов заданий, фильтры по сложности и темам, разборы решений.',
            'url'         => Yii::$app->params['siteUrl'] . '/tasks',
        ]);
        $this->view->title = 'Конструктор таблиц истинности';
        return $this->render('truth-table');
    }

    public function actionFano()
    {
        \app\helpers\Seo::set($this->view, [
            'title'       => 'Задачи ЕГЭ по информатике — все 27 заданий с разборами',
            'description' => 'Каталог задач для подготовки к ЕГЭ по информатике: все 27 типов заданий, фильтры по сложности и темам, разборы решений.',
            'url'         => Yii::$app->params['siteUrl'] . '/tasks',
        ]);
        $this->view->title = 'Условие Фано — проверка и декодирование';
        return $this->render('fano');
    }

    public function actionGraphEditor()
    {

        \app\helpers\Seo::set($this->view, [
            'title'       => 'Задачи ЕГЭ по информатике — все 27 заданий с разборами',
            'description' => 'Каталог задач для подготовки к ЕГЭ по информатике: все 27 типов заданий, фильтры по сложности и темам, разборы решений.',
            'url'         => Yii::$app->params['siteUrl'] . '/tasks',
        ]);
        $this->view->title = 'Редактор графов — построение, таблица смежности, маршруты';
        return $this->render('graph-editor');
    }
}
