<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer}}".
 *
 * @property integer $id
 * @property string $name
 */
class Customer extends ActiveRecord
{
    public const QUALITY_ACTIVE = 'active';
    public const QUALITY_REJECTED = 'rejected';
    public const QUALITY_COMMUNITY = 'community';
    public const QUALITY_UNASSIGNED = 'unassigned';
    public const QUALITY_TRICKLE = 'trickle';

    public const TYPE_LEAD = 'lead';
    public const TYPE_DEAL = 'deal';
    public const TYPE_LOAN = 'loan';

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getQualityTexts(): array
    {
        return [
            self::QUALITY_ACTIVE => Yii::t('app', 'Active'),
            self::QUALITY_REJECTED => Yii::t('app', 'Rejected'),
            self::QUALITY_COMMUNITY => Yii::t('app', 'Community'),
            self::QUALITY_UNASSIGNED => Yii::t('app', 'Unassigned'),
            self::QUALITY_TRICKLE => Yii::t('app', 'Trickle'),
        ];
    }

    /**
     * @param string|null $quality
     * @return string|null
     */
    public static function getQualityTextByQuality(?string $quality):? string
    {
        if ($quality === null) {
            return null;
        }
        return self::getQualityTexts()[$quality] ?? $quality;
    }

    /**
     * @return array<string, string>
     */
    public static function getTypeTexts(): array
    {
        return [
            self::TYPE_LEAD => Yii::t('app', 'Lead'),
            self::TYPE_DEAL => Yii::t('app', 'Deal'),
            self::TYPE_LOAN => Yii::t('app', 'Loan'),
        ];
    }

    /**
     * @param string|null $type
     * @return string|null
     */
    public static function getTypeTextByType(?string $type):? string
    {
        if ($type === null) {
            return null;
        }
        return self::getTypeTexts()[$type] ?? $type;
    }
}
