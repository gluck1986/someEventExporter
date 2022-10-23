<?php

namespace app\widgets\HistoryList\service;

use app\models\History;
use app\models\Task;
use app\widgets\HistoryList\helpers\HistoryListHelper;
use Exception;
use yii\widgets\ListView;

class TaskStrategy implements ItemStrategy
{

    /**
     * @param History $model
     * @param $key
     * @param int $index
     * @param ListView $listView
     * @return string
     * @throws Exception
     */
    public function getContent($model, $key, int $index, ListView $listView): string
    {
        $task = $model->morphObject;
        if ($task !== null && ! ($task instanceof Task)) {
            throw new Exception('morphObject must be Task, given: ' . get_class($task));
        }

        return $listView->render('@app/widgets/HistoryList/views/_item_common', [
            'user' => $model->user,
            'body' => HistoryListHelper::getBodyByModel($model),
            'iconClass' => 'fa-check-square bg-yellow',
            'footerDatetime' => $model->ins_ts,
            'footer' => $task && isset($task->customerCreditor->name) ? 'Creditor: ' . $task->customerCreditor->name : '',
            'bodyDatetime' => null,
            'content' => '',
        ]);
    }
}
