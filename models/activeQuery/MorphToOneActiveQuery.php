<?php

namespace app\models\activeQuery;

use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\db\BaseActiveRecord;

/**
 * todo it can only read
 */
class MorphToOneActiveQuery extends ActiveQuery
{
    /**
     * @var string
     */
    public $relationField;

    /**
     * @var string
     */
    public $idField;


    /**
     * @description [field => class]
     * @var array<string, string>
     */
    public $relationMap = [];

    /**
     * @param string $relationField
     * @param string $idField
     * @param list<class-string> $morphClasses
     * @return self
     */
    public static function make(string $relationField, string $idField, array $morphClasses): self
    {
        $instance = \Yii::createObject(self::class, ['']);
        $instance->relationField = $relationField;
        $instance->idField = $idField;
        foreach ($morphClasses as $morphClass) {
            $fieldValue = $instance->getObjectByTableClassName($morphClass);
            $instance->relationMap[$fieldValue] = $morphClass;
        }

        return $instance;
    }

    /**
     * @param string $className
     * @return string
     */
    public static function getObjectByTableClassName(string $className): string
    {
        if (method_exists($className, 'tableName')) {
            return str_replace(['{', '}', '%'], '', $className::tableName());
        }

        return $className;
    }

    /**
     * Finds the related records for the specified primary record.
     * This method is invoked when a relation of an ActiveRecord is being accessed lazily.
     * @param string $name the relation name
     * @param ActiveRecordInterface|BaseActiveRecord $model the primary model
     * @return mixed the related record(s)
     * @throws InvalidArgumentException if the relation is invalid
     */
    public function findFor($name, $model)
    {
        $class = $this->relationMap[$model[$this->relationField]] ?? null;
        $id = $model[$this->idField] ?? null;
        if ($class === null || $id === null) {
            return null;
        }
        $this->link = ['id' => $this->idField];
        $this->modelClass = $class;
        
        return parent::findFor($name, $model);
    }

    /**
     * @param $name
     * @param array<array-key, ActiveRecord> $primaryModels
     * @return array
     */
    public function populateRelation($name, &$primaryModels)
    {
        $idsGroups = $this->prepareIds($primaryModels);
        $resultGroups = $this->getModels($idsGroups);
        $this->populatePrimary($primaryModels, $resultGroups, $name);

        return [];
    }

    /**
     * @param array $primaryModels
     * @return array<class-string<ActiveRecord>, list<scalar>>
     */
    public function prepareIds(array $primaryModels): array
    {
        $idsGroups = [];
        foreach ($primaryModels as $model) {
            $morphName = $model->{$this->relationField} ?? '';
            $morphClass = $this->relationMap[$morphName] ?? null;
            if ($morphClass === null) {
                continue;
            }
            $morphId = $model->{$this->idField} ?? null;
            if ($morphId === null) {
                continue;
            }
            $idsGroups[$morphClass][] = $morphId;
        }
        foreach (array_keys($idsGroups) as $groupName) {
            $idsGroups[$groupName] = array_unique($idsGroups[$groupName]);
        }
        
        return $idsGroups;
    }

    /**
     * @param array $idsGroups
     * @return array<class-string<ActiveRecord>, array<array-key, ActiveRecord>>
     */
    public function getModels(array $idsGroups): array
    {
        $resultGroups = [];
        /** @var class-string<ActiveRecord> $class */
        foreach ($idsGroups as $class => $ids) {
            $resultGroups[$class] = $class::find()->where(['id' => $ids])->indexBy('id')->all();
        }
        return $resultGroups;
    }

    /**
     * @param array<array-key, ActiveRecord> $primaryModels
     * @param array<class-string<ActiveRecord>, array<array-key, ActiveRecord>> $resultGroups
     * @param string $name
     * @return void
     */
    public function populatePrimary(array $primaryModels, array $resultGroups, string $name): void
    {
        foreach (array_keys($primaryModels) as $modelIndex) {
            $morphName = $primaryModels[$modelIndex][$this->relationField] ?? '';
            $morphClass = $this->relationMap[$morphName] ?? null;
            if ($morphClass === null) {
                continue;
            }
            $morphId = $primaryModels[$modelIndex][$this->idField] ?? null;
            if ($morphId === null) {
                continue;
            }
            $morphObject = $resultGroups[$morphClass][$morphId] ?? null;
            if ($morphObject === null) {
                continue;
            }
            if ($primaryModels[$modelIndex] instanceof ActiveRecordInterface) {
                $primaryModels[$modelIndex]->populateRelation($name, $morphObject);
            } else {
                $primaryModels[$modelIndex][$name] = $morphObject;
            }
        }
    }
}
