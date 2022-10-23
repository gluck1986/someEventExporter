<?php

/**
 * @var $this yii\web\View
 * @var $model History
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $exportType string
 * @var string $filename
 */

use app\models\History;
use app\widgets\Export\ExportCsvStream;
use app\widgets\HistoryList\helpers\HistoryListHelper;

echo ExportCsvStream::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'ins_ts',
            'label' => Yii::t('app', 'Date'),
            'format' => 'datetime'
        ],
        [
            'label' => Yii::t('app', 'User'),
            'value' => function (History $model) {
                return isset($model->user) ? $model->user->username : Yii::t('app', 'System');
            }
        ],
        [
            'label' => Yii::t('app', 'Type'),
            'value' => function (History $model) {
                return $model->object;
            }
        ],
        [
            'label' => Yii::t('app', 'Event'),
            'value' => function (History $model) {
                return $model->eventText;
            }
        ],
        [
            'label' => Yii::t('app', 'Message'),
            'value' => function (History $model) {
                return strip_tags(HistoryListHelper::getBodyByModel($model));
            }
        ]
    ],
    'exportType' => $exportType,
    'batchSize' => 2000,
    'filename' => $filename
]);
