<?php

namespace app\widgets\HistoryList\service;

use app\models\History;
use yii\widgets\ListView;

class ItemStrategyAdapter
{
    /**
     * @var array<string, class-string>
     */
    public $strategyMap = [];

    public function __construct(array $strategyMap)
    {
        $this->strategyMap = $strategyMap;
    }

    public static function defaultInstance(): self
    {
        return new self(self::getDefaultStrategyMap());
    }

    public static function getDefaultStrategyMap(): array
    {
        return [
            History::EVENT_CREATED_TASK => TaskStrategy::class,
            History::EVENT_COMPLETED_TASK => TaskStrategy::class,
            History::EVENT_UPDATED_TASK => TaskStrategy::class,
            History::EVENT_INCOMING_SMS => SmsStrategy::class,
            History::EVENT_OUTGOING_SMS => SmsStrategy::class,
            History::EVENT_OUTGOING_FAX => FaxStrategy::class,
            History::EVENT_INCOMING_FAX => FaxStrategy::class,
            History::EVENT_CUSTOMER_CHANGE_TYPE => CustomerChangeTypeStrategy::class,
            History::EVENT_CUSTOMER_CHANGE_QUALITY => CustomerChangeQualityStrategy::class,
            History::EVENT_INCOMING_CALL => CallStrategy::class,
            History::EVENT_OUTGOING_CALL => CallStrategy::class,
        ];
    }

    /**
     * @param History $model
     * @param $key
     * @param int $index
     * @param ListView $listView
     * @return string
     */
    public function __invoke($model, $key, int $index, ListView $listView): string
    {
        return $this->getStrategy($model->event)->getContent($model, $key, $index, $listView);
    }

    protected function getStrategy(string $strategyKey): ItemStrategy
    {
        return !empty($this->strategyMap[$strategyKey])
            ? new $this->strategyMap[$strategyKey]()
            : $this->getCommonStrategy();
    }

    protected function getCommonStrategy(): ItemStrategy
    {
        return new CommonStrategy();
    }
}
