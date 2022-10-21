<?php

namespace app\controllers;

use app\models\search\HistorySearch;
use Yii;
use yii\web\Controller;

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }


    /**
     * @param string $exportType
     * @return string
     */
    public function actionExport(string $exportType): string
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2048M');
        $model = new HistorySearch();

        $filename = 'history';
        $filename .= '-' . time();

        return $this->render('export', [
            'dataProvider' => $model->search(Yii::$app->request->queryParams),
            'exportType' => $exportType,
            'filename' => $filename
        ]);
    }
}
