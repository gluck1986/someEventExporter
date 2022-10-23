<?php

namespace app\widgets\HistoryList\service;

use app\models\Fax;
use app\models\History;
use app\widgets\HistoryList\helpers\HistoryListHelper;
use Exception;
use Yii;
use yii\helpers\Html;
use yii\widgets\ListView;

class FaxStrategy implements ItemStrategy
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
        $fax = $model->parsedObject;
        if (! ($fax instanceof Fax)) {
            throw new Exception('parsedObject must be Fax, given: ' . get_class($fax));
        }

        return $listView->render('@app/widgets/HistoryList/views/_item_common', [
            'user' => $model->user,
            'body' => HistoryListHelper::getBodyByModel($model) .
                ' - ' .
                (isset($fax->document) ? Html::a(
                    Yii::t('app', 'view document'),
                    $fax->document->getViewUrl(),
                    [
                        'target' => '_blank',
                        'data-pjax' => 0
                    ]
                ) : ''),
            'footer' => Yii::t('app', '{type} was sent to {group}', [
                'type' => $fax ? $fax->getTypeText() : 'Fax',
                'group' => isset($fax->creditorGroup)
                    ? Html::a($fax->creditorGroup->name, ['creditors/groups'], ['data-pjax' => 0])
                    : ''
            ]),
            'footerDatetime' => $model->ins_ts,
            'iconClass' => 'fa-fax bg-green',
            'bodyDatetime' => null,
            'content' => null,
        ]);
    }
}
