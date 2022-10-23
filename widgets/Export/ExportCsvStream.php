<?php

namespace app\widgets\Export;

use Exception;
use kartik\export\ExportMenu;
use yii\data\ArrayDataProvider;
use yii\grid\Column;
use yii\grid\DataColumn;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;

class ExportCsvStream extends ExportMenu
{
    /**
     * @throws Exception
     */
    public function generateBody(): void
    {
        $columns = $this->getVisibleColumns();
        if (count($columns) == 0) {
            return;
        }
        $models = array_values($this->_provider->getModels());
        // do not execute multiple COUNT(*) queries
        $totalCount = $this->_provider->getTotalCount();
        while (count($models) > 0) {
            $keys = $this->_provider->getKeys();
            if ($this->_provider instanceof ArrayDataProvider) {
                $models = array_values($models);
            }
            foreach ($models as $index => $model) {
                $key = $keys[$index];
                $this->streamRow($model, $key, $this->_endRow);
            }
            if ($this->_provider->pagination) {
                $this->_provider->pagination->page++;
                $this->_provider->refresh();
                $this->_provider->setTotalCount($totalCount);
                $models = $this->_provider->getModels();
            } else {
                $models = [];
            }
        }
    }

    /**
     * Generates an output data row with the given data model and key.
     *
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key associated with the data model
     * @param integer $index the zero-based index of the data model among the model array returned by [[dataProvider]].
     * @throws Exception
     */
    public function streamRow($model, $key, int $index)
    {
        /**
         * @var Column $column
         */
        $values = [];
        foreach ($this->getVisibleColumns() as $column) {
            $format = $this->enableFormatter && isset($column->format) ? $column->format : 'raw';
            $value = null;
            if ($column instanceof SerialColumn) {
                $value = $index + 1;
                $pagination = $column->grid->dataProvider->getPagination();
                if ($pagination !== false) {
                    $value += $pagination->getOffset();
                }
            } elseif (isset($column->content)) {
                $value = call_user_func($column->content, $model, $key, $index, $column);
            } elseif (method_exists($column, 'getDataCellValue')) {
                $value = $column->getDataCellValue($model, $key, $index);
            } elseif (isset($column->attribute)) {
                $value = ArrayHelper::getValue($model, $column->attribute, '');
            }
            $this->_endCol++;
            if (isset($value) && $value !== '' && isset($format)) {
                $value = $this->formatter->format($value, $format);
            } else {
                $value = '';
            }
            $values[] = "\"$value\"";
        }

        $this->write($values);
    }

    public function run()
    {
        $this->initI18N(__DIR__);
        $this->initColumnSelector();
        $this->setVisibleColumns();
        $this->initExport();

        if ($this->timeout >= 0) {
            set_time_limit($this->timeout);
        }
        $filename = static::sanitize($this->filename);
        $file = $filename . '.' . 'csv';
        $this->beginStream($file);
        $this->generateHeader();
        $this->generateBody();
        /** @todo  $this->generateFooter();*/
        exit();
    }

    /**
     * Generates the output data header content.
     */
    public function generateHeader()
    {
        $values = [];
        $columns = $this->getVisibleColumns();
        if (count($columns) == 0) {
            return;
        }
        foreach ($this->getVisibleColumns() as $column) {
            /**
             * @var DataColumn $column
             */
            $head = ($column instanceof DataColumn) ? $this->getColumnHeader($column) : $column->header;
            $values[] = "\"$head\"";
        }
        $this->write($values);
    }

    public function beginStream(string $fileName)
    {
        ob_end_clean();
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Pragma: public');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
    }

    /**
     * @param array $values
     * @return void
     */
    public function write(array $values): void
    {
        $data = implode(',', $values) . PHP_EOL;
        echo $data;
        ob_flush();
    }
}
