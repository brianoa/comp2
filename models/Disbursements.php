<?php

namespace app\models;

use Yii;
use Webpatser\Uuid\Uuid;
use app\components\Keys;
use app\components\Myhelper;
use app\components\DisburseJob;
use yii\db\IntegrityException;

/**
 * This is the model class for table "disbursements".
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
 */
class Disbursements extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'disbursements';
    }
    /*command id comission - SalaryPayment
    *expenses - BusinessPayment
    *winner payments - PromotionPayment
    */
    public static function cokePayout($id)
    {
        $req=["id"=>$id];
        $url="https://mt.comp21.co.ke/coke/disburse";
        $headers=['Content-Type: application/json','Authorization:'.DEPOSIT_AUTHORIZATION];
        Myhelper::curlPost(json_encode($req),$headers,$url);
    }
    public static function netPayout($id)
    {
        $req=["id"=>$id];
        $url="https://mt.comp21.net/net/disburse";
        $headers=['Content-Type: application/json','Authorization:'.DEPOSIT_AUTHORIZATION];
        Myhelper::curlPost(json_encode($req),$headers,$url);
    }
    /**
     * Getter for users full name
     * @return string
     */
    public function getFullname() {
        if ( isset( $this->user->first_name ) ) {
                return $this->user->first_name;
        }
    }
    
    /**
        * Customer - Stations relationship
        * @return \yii\db\ActiveQuery
    */
    public function getUser() {
        return $this->hasOne(Users::className(), [ 'id' => '' ] );
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','amount','disbursement_type','phone_number','reference_name'], 'required'],
            [['amount'], 'number','max' => 3000000],
            [['status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['phone_number'], 'string', 'max' => 12,'min' => 12],
            [['unique_field','station_id'], 'string', 'max' => 50],
            [['reference_id', 'disbursement_type', 'transaction_reference'], 'string', 'max' => 100],
            [['reference_name','conversation_id'], 'string', 'max' => 255],
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
        ];
    }
    public static function saveDisbursement($reference_id,$reference_name,$phone_number,$amount,$disbursement_type,$status,$station_id)
    {

        if($amount <= MAX_AMOUNT)
            {
                $unique_field=$phone_number.$amount.date('YmdHi');
               Disbursements::createDisbursement($reference_id,$reference_name,$phone_number,$amount,$disbursement_type,$status,$unique_field,$station_id); 
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
                    Disbursements::createDisbursement($reference_id,$reference_name,$phone_number,$to_pay,$disbursement_type,$status,$unique_field,$station_id);
                }
            }
        
        
        
    }
    public static function createDisbursement($reference_id,$reference_name,$phone_number,$amount,$disbursement_type,$status,$unique_field,$station_id)
    {
        try {
            $model=new Disbursements();
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
            $telco = Myhelper::getOperator($phone_number);
            if($status == 0)
            {
                Yii::$app->queue->push(new DisburseJob(['id'=>$model->id, "telco" => $telco]));
            }
            
            
        } catch (IntegrityException $e) {
            //allow execution
        }
    }
    public static function getPendingDisbursement()
    {
        return Disbursements::find()->where("status=0")->orderBy("created_at ASC")->all();
    }
    public static  function generateTokenB2C()
    {

        $url = MPESATOKENURL;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $app_consumer_key = Keys::getMpesaConsumerKey();
        $app_consumer_secret = Keys::getMpesaConsumerSecret();
        $credentials = base64_encode($app_consumer_key.':'.$app_consumer_secret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $token_info=json_decode($curl_response,true);
        return $token_info['access_token'];

    }
    public static function setSecurityCredentials ()
    {
        $publicKey =Keys::getMpesaPublicKey();
        $plaintext =Keys::getMpesaPlainText();
        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }
    /*command id comission - SalaryPayment
    *expenses - BusinessPayment
    *winner payments - PromotionPayment
    */ 
    public static function getCommandId($disbursement_type)
    {
        switch($disbursement_type)
        {
            case "winning":
                $command_id="PromotionPayment";
                break;
            case "commission":
                $command_id="SalaryPayment";
                break;
            case "presenter_commission":
                $command_id="SalaryPayment";
                break;
            case "management_commission":
                $command_id="SalaryPayment";
                break;
            case "expenses":
                $command_id="BusinessPayment";
                break;
            default:
                $command_id="PromotionPayment";
                break;
        }
        return $command_id;
    }
    public static function checkDuplicate($reference_id,$phone_number,$amount)
    {
        return Disbursements::find()->where("reference_id='$reference_id'")
        ->andWhere("phone_number=$phone_number")
        ->andWhere("amount=$amount")->count();
    }
    public static function getDuplicates()
    {
        $sql='SELECT COUNT(unique_field) AS total,unique_field FROM disbursements  GROUP BY unique_field HAVING(total > 1)  LIMIT  10000';
        return Yii::$app->db->createCommand($sql)
        ->queryAll();
    }
    public static function removeDups($unique_field,$limits)
    {
        $sql='DELETE FROM disbursements WHERE unique_field=:unique_field LIMIT :limits';
        Yii::$app->db->createCommand($sql)
        ->bindValue(':unique_field',$unique_field)
        ->bindValue(':limits',$limits)
        ->execute();
    }
    
     /**
     * Method to get disbursement by station report
     * @param type $start_date
     * @param type $end_date
     * @return type
     */
    public static function getDisbursementByStation($start_date,$end_date)
    {
        
        $sql = "SELECT sum(d.amount) AS totalamount, s.name
        FROM disbursements d
        LEFT JOIN winning_histories w ON d.reference_id = w.id
        LEFT JOIN stations s ON w.station_id = s.id
        WHERE ";
        if(\Yii::$app->myhelper->isStationManager()){
            $stations = implode(",", array_map(function($string) {
               return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $sql .=" w.station_id IN ($stations) AND ";
        }
        $sql .= "disbursement_type = 'winning' AND d.created_at BETWEEN  :start_date AND :end_date
        GROUP BY s.name";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':start_date',$start_date)
        ->bindValue(':end_date',$end_date)
        ->queryAll();
    }
        /*command id comission - SalaryPayment
    *expenses - BusinessPayment
    *winner payments - PromotionPayment
    */
    public static function devPayout($disbursement_id)
    {
        $row=Disbursements::findOne($disbursement_id);
        $phone_number=$row->phone_number;
        $amount=$row->amount;
        $row->status=3;
        $row->save(false);
        $command_id=Disbursements::getCommandId($row->disbursement_type);
        $access_token = Disbursements::generateTokenB2C();
        $CommandID = $command_id;
        $PartyA = PARTYA;
        $Remarks = REMARKS;
        $QueueTimeOutURL = QUEUETIMEOUTURL;
        $ResultURL = RESULTURL;
        $InitiatorName = INITIATORNAME;
        $Occasion = OCCASION;
        $url = MPESAPAYMENTREQUESTURL;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '. $access_token));

        $curl_post_data = array(
            //Fill in the request parameters with valid values
            'InitiatorName' => $InitiatorName,
            'SecurityCredential' => Disbursements::setSecurityCredentials(),
            'CommandID' => $CommandID,
            'Amount' => $amount,
            'PartyA' => $PartyA,
            'PartyB' => $phone_number,
            'Remarks' => $Remarks,
            'QueueTimeOutURL' => $QueueTimeOutURL,
            'ResultURL' => $ResultURL,
            'Occasion' => $Occasion
        );

        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);
        $content = json_decode($curl_response,true);
        if(isset($content['ConversationID']))
        {
            $conversation_id = $content['ConversationID'];
            $model=Disbursements::findOne($disbursement_id);
            if($model)
            {
                $model->conversation_id=$conversation_id;
                $model->updated_at=date("Y-m-d H:i:s");
                $model->save(false);
            }
        }
        else
        {
            $filename="/srv/apps/comp21/web/mpesa.txt";
            $data=$curl_response;
            file_put_contents( $filename, $data,FILE_APPEND);
        }

        
    }
    public static function tzPayout($id,$product,$telco)
    {
        $req=["id"=>$id,"product"=>$product,"telco"=>$telco];

        if($product == "mchongo")
        {
            $req=json_encode($req);
            $url=TIGO_PAY_URL;
            $headers=['Content-Type: application/json','Authorization:'.DEPOSIT_AUTHORIZATION];
            Myhelper::curlPost($req,$headers,$url);
        }
        if($product == "bomba")
        {
            if($telco == "vodacom")
            {
                if($_SERVER["HTTP_HOST"] == "mshikoplus.com")
                {
                    $req["product"] = "tbc";
                    $url = "https://api.mchezoradio.com/api/disbursevodacom";
                }else{
                    $url=VODA_PAY_URL;
                }
                $req=json_encode($req);
                $headers=['Content-Type: application/json','Authorization:'.DEPOSIT_AUTHORIZATION];
                Myhelper::curlPost($req,$headers,$url);
            }else {
                if($_SERVER["HTTP_HOST"] == "mshikoplus.com")
                {
                    $req["product"] = "tbc";
                    $url = "https://api.mchezoradio.com/api/tzdisburse";
                }else{
                    $url=TIGO_PAY_URL;
                }
                $req=json_encode($req);
                $headers=['Content-Type: application/json','Authorization:'.DEPOSIT_AUTHORIZATION];
                var_dump(Myhelper::curlPost($req,$headers,$url));
            }

        }
        if($product == "supa") 
        {
            if($telco == "vodacom")
            {
                $req=json_encode($req);
                $url=SUPA_VODA_PAY_URL;
                $headers=['Content-Type: application/json','Authorization:'.DEPOSIT_AUTHORIZATION];
                Myhelper::curlPost($req,$headers,$url);
            }else{
                $req=json_encode($req);
                $url=TIGO_PAY_URL;
                $headers=['Content-Type: application/json','Authorization:'.DEPOSIT_AUTHORIZATION];
                Myhelper::curlPost($req,$headers,$url);
            }
        }

        echo $url;

       
        
    }
    public static function zambiaPayout($id,$product)
    {
        $req=["id"=>$id,"product"=>$product];
        $req=json_encode($req);
        $url=ZAMBIA_PAY_URL;
        $headers=['Content-Type: application/json','Authorization:'.ZAMBIA_PAY_TOKEN];
        Myhelper::curlPost($req,$headers,$url);
    }
}
