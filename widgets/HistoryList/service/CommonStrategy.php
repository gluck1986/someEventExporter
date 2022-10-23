<?php

namespace app\widgets\HistoryList\service;

use app\models\History;
use app\widgets\HistoryList\helpers\HistoryListHelper;
use yii\widgets\ListView;

class CommonStrategy implements ItemStrategy
{
    /**
     * @param History $model
     * @param $key
     * @param int $index
     * @param ListView $listView
     * @return string
     */
    public function getContent($model, $key, int $index, ListView $listView): string
    {
        return $listView->render('@app/widgets/HistoryList/views/_item_common', [
            'user' => $model->user,
            'body' => HistoryListHelper::getBodyByModel($model),
            'bodyDatetime' => $model->ins_ts,
            'iconClass' => 'fa-gear bg-purple-light',
            'content' => null,
            'footer' => null,
            'footerDatetime' => null,
        ]);
    }
}
