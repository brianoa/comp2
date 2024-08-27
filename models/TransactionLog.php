<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaction_log".
 *
 * @property string $id
 * @property string $json_data
 * @property string $date
 * @property string|null $api_type
 * @property int $state
 */
class TransactionLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaction_log';
    }
    public static function getDb() {
        return Yii::$app->mpesa_db;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'json_data', 'date'], 'required'],
            [['json_data'], 'string'],
            [['date'], 'safe'],
            [['state'], 'integer'],
            [['id'], 'string', 'max' => 36],
            [['api_type'], 'string', 'max' => 20]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'json_data' => 'Json Data',
            'date' => 'Date',
            'api_type' => 'Api Type',
            'state' => 'State',
        ];
    }
}
