<?php

namespace app\models;

use Yii;
use app\models\WinnerSummary;
use yii\db\IntegrityException;

/**
 * This is the model class for table "winning_histories".
 *
 * @property string $id
 * @property string|null $prize_id
 * @property string|null $station_show_prize_id
 * @property string $reference_name
 * @property string $reference_phone
 * @property string $reference_code
 * @property string|null $station_id
 * @property string|null $presenter_id
 * @property string|null $station_show_id
 * @property float $amount
 * @property float $transaction_cost
 * @property string|null $conversation_id
 * @property string|null $transaction_reference
 * @property int $status
 * @property string|null $remember_token
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class DeletedWinner extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'deleted_winner';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'reference_name', 'reference_phone', 'reference_code'], 'required'],
            [['amount', 'transaction_cost'], 'number'],
            [['status', 'notified'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['prize_id', 'station_show_prize_id', 'conversation_id', 'transaction_reference', 'remember_token'], 'string', 'max' => 100],
            [['reference_name', 'reference_phone', 'reference_code', 'station_id', 'presenter_id', 'station_show_id', 'unique_field'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'prize_id' => 'Prize ID',
            'station_show_prize_id' => 'Station Show Prize ID',
            'reference_name' => 'Reference Name',
            'reference_phone' => 'Reference Phone',
            'reference_code' => 'Reference Code',
            'station_id' => 'Station ID',
            'presenter_id' => 'Presenter ID',
            'station_show_id' => 'Station Show ID',
            'amount' => 'Amount',
            'transaction_cost' => 'Transaction Cost',
            'conversation_id' => 'Conversation ID',
            'transaction_reference' => 'Transaction Ref',
            'status' => 'Status',
            'remember_token' => 'Remember Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'stationshowprizeamount' => 'Prizeamount',
            'stationshowname' => 'Showname',
            'stationname' => 'Station',
            'notified' => 'Notified'
        ];
    }
}
