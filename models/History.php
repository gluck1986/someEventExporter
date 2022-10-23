<?php

namespace app\models;

use app\models\activeQuery\MorphToOneActiveQuery;
use app\models\traits\ObjectNameTrait;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%history}}".
 *
 * @property integer $id
 * @property string $ins_ts
 * @property integer $customer_id
 * @property string $event
 * @property string $object
 * @property integer $object_id
 * @property string $message
 * @property string $detail
 * @property integer $user_id
 *
 * @property string $eventText
 *
 * @property Customer $customer
 * @property User $user
 *
 * @property ActiveRecord|null $parsedObject
 * @property Task|null $task
 * @property Sms|null $sms
 * @property Call|null $call
 */
class History extends ActiveRecord
{
    use ObjectNameTrait;

    public const DETAIL_CHANGED_ATTRIBUTES_PROPERTY = 'changedAttributes';
    public const DETAIL_DATA_PROPERTY = 'data';

    public const EVENT_CREATED_TASK = 'created_task';
    public const EVENT_UPDATED_TASK = 'updated_task';
    public const EVENT_COMPLETED_TASK = 'completed_task';

    public const EVENT_INCOMING_SMS = 'incoming_sms';
    public const EVENT_OUTGOING_SMS = 'outgoing_sms';

    public const EVENT_INCOMING_CALL = 'incoming_call';
    public const EVENT_OUTGOING_CALL = 'outgoing_call';

    public const EVENT_INCOMING_FAX = 'incoming_fax';
    public const EVENT_OUTGOING_FAX = 'outgoing_fax';

    public const EVENT_CUSTOMER_CHANGE_TYPE = 'customer_change_type';
    public const EVENT_CUSTOMER_CHANGE_QUALITY = 'customer_change_quality';

    protected $_objectClasses = [
        Customer::class,
        Sms::class,
        Task::class,
        Call::class,
        Fax::class,
        User::class,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['ins_ts'], 'safe'],
            [['customer_id', 'object_id', 'user_id'], 'integer'],
            [['event'], 'required'],
            [['message', 'detail'], 'string'],
            [['event', 'object'], 'string', 'max' => 255],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ins_ts' => Yii::t('app', 'Ins Ts'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'event' => Yii::t('app', 'Event'),
            'object' => Yii::t('app', 'Object'),
            'object_id' => Yii::t('app', 'Object ID'),
            'message' => Yii::t('app', 'Message'),
            'detail' => Yii::t('app', 'Detail'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }

    public function getParsedObject(): ActiveQuery
    {
        return MorphToOneActiveQuery::make('object', 'object_id', $this->_objectClasses);
    }

    /**
     * @return ActiveQuery
     */
    public function getCustomer(): ActiveQuery
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return array<string, string>
     */
    public static function getEventTexts(): array
    {
        return [
            self::EVENT_CREATED_TASK => Yii::t('app', 'Task created'),
            self::EVENT_UPDATED_TASK => Yii::t('app', 'Task updated'),
            self::EVENT_COMPLETED_TASK => Yii::t('app', 'Task completed'),

            self::EVENT_INCOMING_SMS => Yii::t('app', 'Incoming message'),
            self::EVENT_OUTGOING_SMS => Yii::t('app', 'Outgoing message'),

            self::EVENT_CUSTOMER_CHANGE_TYPE => Yii::t('app', 'Type changed'),
            self::EVENT_CUSTOMER_CHANGE_QUALITY => Yii::t('app', 'Property changed'),

            self::EVENT_OUTGOING_CALL => Yii::t('app', 'Outgoing call'),
            self::EVENT_INCOMING_CALL => Yii::t('app', 'Incoming call'),

            self::EVENT_INCOMING_FAX => Yii::t('app', 'Incoming fax'),
            self::EVENT_OUTGOING_FAX => Yii::t('app', 'Outgoing fax'),
        ];
    }

    /**
     * @param string $event
     * @return string
     */
    public static function getEventTextByEvent(string $event): string
    {
        return static::getEventTexts()[$event] ?? $event;
    }

    /**
     * @return string
     */
    public function getEventText(): string
    {
        return static::getEventTextByEvent($this->event);
    }


    public function getParsedDetail(): ?\stdClass
    {
        try {
            $detail = json_decode($this->detail);
            if (!($detail instanceof \stdClass)) {
                return null;
            }
            return $detail;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param string $attribute
     * @return \stdClass|null
     */
    public function getDetailChangedAttribute(string $attribute): ?\stdClass
    {
        $detail = $this->getParsedDetail();
        if (!property_exists($detail, self::DETAIL_CHANGED_ATTRIBUTES_PROPERTY)) {
            return null;
        }
        if (!property_exists($detail->{self::DETAIL_CHANGED_ATTRIBUTES_PROPERTY}, $attribute)) {
            return null;
        }
        $attributeValue = $detail->{self::DETAIL_CHANGED_ATTRIBUTES_PROPERTY}->{$attribute} ?? null;
        if (!($attributeValue instanceof \stdClass)) {
            return null;
        }
        return $attributeValue;
    }

    /**
     * @param $attribute
     * @return string|null
     */
    public function getDetailOldValue($attribute):? string
    {
        $detail = $this->getDetailChangedAttribute($attribute);

        return isset($detail->old) ? (string)$detail->old : null;
    }

    /**
     * @param $attribute
     * @return string|null
     */
    public function getDetailNewValue($attribute):? string
    {
        $detail = $this->getDetailChangedAttribute($attribute);

        return isset($detail->new) ? (string)$detail->new : null;
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    public function getDetailData(string $attribute)
    {
        $detail = $this->getParsedDetail();
        if (!property_exists($detail, self::DETAIL_DATA_PROPERTY)) {
            return null;
        }
        if (!property_exists($detail->{self::DETAIL_DATA_PROPERTY}, $attribute)) {
            return null;
        }
        return $detail->{self::DETAIL_DATA_PROPERTY}->{$attribute} ?? null;
    }
}
