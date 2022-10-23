<?php

namespace app\widgets\HistoryList;

use app\models\search\HistorySearch;
use kartik\export\ExportMenu;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;

/**
 *
 * @property-read string $linkExport
 */
class HistoryList extends Widget
{
    /**
     * @return string
     */
    public function run(): string
    {
        $model = new HistorySearch();

        return $this->render('main', [
            'linkExport' => $this->getLinkExport(),
            'dataProvider' => $model->search(Yii::$app->request->queryParams)
        ]);
    }

    /**
     * @return string
     */
    private function getLinkExport(): string
    {
        $params = Yii::$app->getRequest()->getQueryParams();
        $params = ArrayHelper::merge([
            'exportType' => ExportMenu::FORMAT_CSV
        ], $params);
        $params[0] = 'site/export';

        return Url::to($params);
    }
}
