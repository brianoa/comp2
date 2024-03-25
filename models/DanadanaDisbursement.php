<?php

namespace app\models;

use app\components\DisburseJob;
use Webpatser\Uuid\Uuid;
use Yii;
use yii\db\IntegrityException;

/**
 * This is the model class for table "danadana_disbursement".
 *
 * @property string $id
 * @property string|null $reference_id
 * @property string|null $reference_name
 * @property string|null $phone_number
 * @property float $amount
 * @property string|null $conversation_id
 * @property int $status
 * @property string|null $disbursement_type
 * @property string|null $transaction_reference
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $unique_field
 * @property string|null $station_id
 * @property string|null $description
 * @property string|null $descriptio
 */
class DanadanaDisbursement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'danadana_disbursement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['amount'], 'number'],
            [['status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['reference_id', 'disbursement_type', 'transaction_reference'], 'string', 'max' => 100],
            [['reference_name', 'phone_number', 'conversation_id'], 'string', 'max' => 255],
            [['unique_field', 'station_id', 'description', 'descriptio'], 'string', 'max' => 50],
            [['unique_field'], 'unique'],
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
            'reference_id' => 'Reference ID',
            'reference_name' => 'Reference Name',
            'phone_number' => 'Phone Number',
            'amount' => 'Amount',
            'conversation_id' => 'Conversation ID',
            'status' => 'Status',
            'disbursement_type' => 'Disbursement Type',
            'transaction_reference' => 'Transaction Reference',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'unique_field' => 'Unique Field',
            'station_id' => 'Station ID',
            'description' => 'Description',
            'descriptio' => 'Descriptio',
        ];
    }

    public static function saveDisbursement($reference_id,$reference_name,$phone_number,$amount,$disbursement_type,$status,$station_id)
    {

        if($amount <= MAX_AMOUNT)
            {
                $unique_field=$phone_number.$amount.date('YmdHi');
               DanadanaDisbursement::createDisbursement($reference_id,$reference_name,$phone_number,$amount,$disbursement_type,$status,$unique_field,$station_id); 
            }
            else
            {
                $count=0;
                while($amount > 0)
                {
                    if($amount > MAX_AMOUNT)
                    {
                        $to_pay=MAX_AMOUNT;
                        $amount=$amount-MAX_AMOUNT;
                    }
                    else
                    {
                        $to_pay=$amount;
                        $amount=0;
                    }
                    $count++;
                    $unique_field=$phone_number.$amount.date('YmdHi')."-".$count;
                    DanadanaDisbursement::createDisbursement($reference_id,$reference_name,$phone_number,$to_pay,$disbursement_type,$status,$unique_field,$station_id);
                }
            }
        
        
        
    }
    public static function createDisbursement($reference_id,$reference_name,$phone_number,$amount,$disbursement_type,$status,$unique_field,$station_id)
    {
        try {
            $model=new DanadanaDisbursement();
            $model->id=Uuid::generate()->string;
            $model->reference_id=$reference_id;
            $model->station_id=$station_id;
            $model->reference_name=$reference_name;
            $model->phone_number=$phone_number;
            $model->amount=$amount;
            $model->status=$status;
            $model->unique_field=$unique_field;
            $model->disbursement_type=$disbursement_type;
            $model->created_at=date("Y-m-d H:i:s");
            $model->save(false);
            Yii::$app->queue->push(new DisburseJob(['id'=>$model->id]));
            
        } catch (IntegrityException $e) {
            //allow execution
        }
    }
    public static function getPendingDisbursement()
    {
        return DanadanaDisbursement::find()->where("status=0")->orderBy("created_at ASC")->all();
    }

    public static function removeDups($unique_field,$limits)
    {
        $sql='DELETE FROM danadana_disbursement WHERE unique_field=:unique_field LIMIT :limits';
        Yii::$app->db->createCommand($sql)
        ->bindValue(':unique_field',$unique_field)
        ->bindValue(':limits',$limits)
        ->execute();
    }
}
