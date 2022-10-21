<?php

namespace app\models\traits;

use app\models\Call;
use app\models\Customer;
use app\models\Fax;
use app\models\Sms;
use app\models\Task;
use app\models\User;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;

trait ObjectNameTrait
{
    public static $classes = [
        Customer::class,
        Sms::class,
        Task::class,
        Call::class,
        Fax::class,
        User::class,
    ];

    /**
     * @param string $name
     * @param bool $throwException
     * @return ActiveQueryInterface|ActiveQuery|null
     */
    public function getRelation($name, $throwException = true)
    {
        $getter = 'get' . $name;
        $class = self::getClassNameByRelation($name);

        if (!method_exists($this, $getter) && $class) {
            return $this->hasOne($class, ['id' => 'object_id']);
        }

        return parent::getRelation($name, $throwException);
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
     * @param string $relation
     * @return string|null
     */
    public static function getClassNameByRelation(string $relation): ?string
    {
        foreach (self::$classes as $class) {
            if (self::getObjectByTableClassName($class) === $relation) {
                return $class;
            }
        }
        return null;
    }
}
