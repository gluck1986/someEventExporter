<?php

namespace app\widgets\HistoryList\helpers;

use app\models\Call;
use app\models\Customer;
use app\models\History;
use app\models\Sms;
use app\models\Task;

class HistoryListHelper
{
    public static function getBodyByModel(History $model): string
    {
        switch ($model->event) {
            case History::EVENT_CREATED_TASK:
            case History::EVENT_COMPLETED_TASK:
            case History::EVENT_UPDATED_TASK:
                $task = $model->morphObject;
                if (!($task instanceof Task)) {
                    return $model->eventText;
                }
                return "$model->eventText: " . ($task->title ?? '');
            case History::EVENT_INCOMING_SMS:
            case History::EVENT_OUTGOING_SMS:
                $sms = $model->morphObject;
                if (!($sms instanceof Sms)) {
                    return '';
                }
                return $sms->message ?: '';
            case History::EVENT_CUSTOMER_CHANGE_TYPE:
                return "$model->eventText " .
                    (Customer::getTypeTextByType($model->getDetailOldValue('type')) ?? 'not set') . ' to ' .
                    (Customer::getTypeTextByType($model->getDetailNewValue('type')) ?? 'not set');
            case History::EVENT_CUSTOMER_CHANGE_QUALITY:
                return "$model->eventText " .
                    (Customer::getQualityTextByQuality($model->getDetailOldValue('quality')) ?? 'not set') . ' to ' .
                    (Customer::getQualityTextByQuality($model->getDetailNewValue('quality')) ?? 'not set');
            case History::EVENT_INCOMING_CALL:
            case History::EVENT_OUTGOING_CALL:
                $call = $model->morphObject;
                if (!($call instanceof Call)) {
                    return '<i>Deleted</i> ';
                }
                return
                    $call->totalStatusText . (
                    $call->getTotalDisposition(false)
                        ? " <span class='text-grey'>" . $call->getTotalDisposition(false) . '</span>'
                        : ''
                    );
            default:
                return $model->eventText;
        }
    }
}
