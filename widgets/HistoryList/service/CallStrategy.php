<?php

namespace app\widgets\HistoryList\service;

use app\models\Call;
use app\models\History;
use app\widgets\HistoryList\helpers\HistoryListHelper;
use Exception;
use yii\widgets\ListView;

class CallStrategy implements ItemStrategy
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
        $call = $model->parsedObject;
        if ($call !== null && ! ($call instanceof Call)) {
            throw new Exception('parsedObject must be Call, given: ' . get_class($call));
        }

        $answered = $call && $call->status === Call::STATUS_ANSWERED;

        return $listView->render('@app/widgets/HistoryList/views/_item_common', [
            'user' => $model->user,
            'content' => $call->comment ?? '',
            'body' => HistoryListHelper::getBodyByModel($model),
            'footerDatetime' => $model->ins_ts,
            'footer' => isset($call->applicant) ? "Called <span>{$call->applicant->name}</span>" : null,
            'iconClass' => $answered ? 'md-phone bg-green' : 'md-phone-missed bg-red',
            'bodyDatetime' => null,
        ]);
    }
}
