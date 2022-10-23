<?php

namespace app\widgets\HistoryList\service;

use yii\widgets\ListView;

interface ItemStrategy
{
    public function getContent($model, $key, int $index, ListView $listView): string;
}
