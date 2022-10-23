<?php

namespace app\widgets\HistoryList\service;

use app\models\History;
use app\models\Sms;
use app\widgets\HistoryList\helpers\HistoryListHelper;
use Exception;
use Yii;
use yii\widgets\ListView;

class SmsStrategy implements ItemStrategy
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
        $sms = $model->parsedObject;
        if (! ($sms instanceof Sms)) {
            throw new Exception('parsedObject must be Sms, given: ' . get_class($sms));
        }

        return  $listView->render('@app/widgets/HistoryList/views/_item_common', [
            'user' => $model->user,
            'body' => HistoryListHelper::getBodyByModel($model),
            'footer' => $sms->direction === Sms::DIRECTION_INCOMING ?
                Yii::t('app', 'Incoming message from {number}', [
                    'number' => $sms->phone_from ?? ''
                ]) : Yii::t('app', 'Sent message to {number}', [
                    'number' => $sms->phone_to ?? ''
                ]),
            'footerDatetime' => $model->ins_ts,
            'iconClass' => 'icon-sms bg-dark-blue',
            'bodyDatetime' => null,
            'content' => null,
        ]);
    }
}
