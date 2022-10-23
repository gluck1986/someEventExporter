<?php

namespace app\widgets\HistoryList\service;

use app\models\Customer;
use app\models\History;
use yii\widgets\ListView;

class CustomerChangeTypeStrategy implements ItemStrategy
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
        return  $listView->render('@app/widgets/HistoryList/views/_item_statuses_change', [
            'model' => $model,
            'oldValue' => Customer::getTypeTextByType($model->getDetailOldValue('type')),
            'newValue' => Customer::getTypeTextByType($model->getDetailNewValue('type')),
            'content' => null,
        ]);
    }
}
