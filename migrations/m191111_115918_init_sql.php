<?php

use yii\db\Migration;

/**
 * Class m191111_115918_init_sql
 */
class m191111_115918_init_sql extends Migration
{
    /**
     * @var string[]
     */
    private $_initTables = [
        'customer',
        'user',
        'history',
        'sms',

        'task',
        'call',
        'fax',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->_initTables as $table) {
            foreach (file(__DIR__ . '/init/' . $table . '.sql') as $sql) {
                $this->execute(trim($sql));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (array_reverse($this->_initTables) as $table) {
            $this->delete($table);
        }
    }
}
