<?php

namespace app\models;

use Yii;
use Webpatser\Uuid\Uuid;
use yii\db\IntegrityException;
use app\components\Myhelper;
use app\components\DisburseJob;
/**
 * This is the model class for table "transaction_histories".
 *
 * @property string $id
 * @property string $mpesa_payment_id
 * @property string $reference_name
 * @property string $reference_phone
 * @property string $reference_code
 * @property string|null $station_id
 * @property string|null $station_show_id
 * @property float $amount
 * @property int $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class ArchivedTransactionHistories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaction_histories';
    }
    public static function getDb() {
        return Yii::$app->analytics_db;
    }
    /**
        * Customer - Stations relationship
        * @return \yii\analytics_db\ActiveQuery
    */
    public function getStations() {
        return $this->hasOne(Stations::className(), [ 'id' => 'station_id' ] );
    }
    
    /**
        * Customer - Stations relationship
        * @return \yii\analytics_db\ActiveQuery
    */
    public function getMpesapayment() {
        return $this->hasOne(MpesaPayments::className(), ['id' => 'mpesa_payment_id']);
    }
    
    /**
        * Customer - Stations relationship
        * @return \yii\analytics_db\ActiveQuery
    */
    public function getStationshows(){
        return $this->hasOne(StationShows::className(), ['id' => 'station_show_id']);
    }
    /**
     * Getter for users full name
     * @return string
     */
    public function getMpesadetails() {
        if (isset($this->mpesapayment->TransID)){
            return $this->mpesapayment->TransID.' '.$this->mpesapayment->BillRefNumber;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'mpesa_payment_id', 'reference_name', 'reference_phone', 'reference_code'], 'required'],
            [['amount'], 'number'],
            [['status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['mpesa_payment_id', 'reference_name', 'reference_phone', 'reference_code', 'station_show_id'], 'string', 'max' => 255],
            [['station_id'], 'string', 'max' => 100],
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
            'mpesa_payment_id' => 'Mpesa Payment ID',
            'reference_name' => 'Reference Name',
            'reference_phone' => 'Reference Phone',
            'reference_code' => 'Reference Code',
            'station_id' => 'Station ID',
            'station_show_id' => 'Station Show ID',
            'amount' => 'Amount',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
    public static function getShowTransactions($station_show_id,$start_time,$end_time)
    {
        $sql="SELECT reference_name,reference_phone,amount,created_at FROM transaction_histories 
        WHERE station_show_id=:station_show_id
        AND deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':station_show_id',$station_show_id)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryAll();
    }
    public static function getJackpotTransactions($start_time,$end_time)
    {
        $sql="SELECT reference_name,reference_phone,amount,created_at FROM transaction_histories 
        WHERE deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryAll();
    }
    public static function getTransactionTotal($station_show_id,$start_time,$end_time)
    {
        $sql="SELECT coalesce(sum(amount),0) as total FROM transaction_histories 
        WHERE station_show_id=:station_show_id
        AND deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':station_show_id',$station_show_id)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryOne();
    }
    public static function getJackpotTransactionTotal($start_time,$end_time)
    {
        $sql="SELECT coalesce(sum(amount),0) as total FROM transaction_histories 
        WHERE deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryOne();
    }
    public static function pickRandom($station_show_id,$past_winners,$from_date)
    {
        $sql="SELECT * FROM transaction_histories WHERE station_show_id=:station_show_id AND created_at >:from_date AND reference_phone NOT IN (" . implode(',', $past_winners) . ") ORDER BY RAND() LIMIT 1";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':station_show_id',$station_show_id)
        ->bindValue(':from_date',$from_date)
        ->queryOne();
    }
    public static function pickJackpot($past_winners,$from_date,$to_date)
    {
        $sql="SELECT * FROM transaction_histories WHERE  created_at BETWEEN :from_date AND :to_date AND reference_phone NOT IN (" . implode(',', $past_winners) . ") ORDER BY RAND() LIMIT 1";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':from_date',$from_date)
        ->bindValue(':to_date',$to_date)
        ->queryOne();
    }
    public static function pickBonusWinners($station_show_id,$past_winners,$from_date,$limit)
    {
        $sql="SELECT count(reference_phone) as total,reference_phone,station_id FROM transaction_histories WHERE station_show_id=:station_show_id AND created_at >:from_date AND reference_phone NOT IN (" . implode(',', $past_winners) . ") group by reference_phone,station_id ORDER BY total DESC LIMIT $limit";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':station_show_id',$station_show_id)
        ->bindValue(':from_date',$from_date)
        ->queryAll();
    }
    public static function getTotalTransactions($from_time)
    {
        $sql="select COALESCE(sum(amount),0) as total_history from 
        transaction_histories where created_at LIKE :from_time";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':from_time',"%$from_time%")
        ->queryOne();
    }
    public static function getTotalTransactionsInRange($from_time,$to_time)
    {
        $sql="select COALESCE(sum(amount),0) as total_history from 
        transaction_histories where created_at >= :from_time and
        created_at <= :to_time";
        return Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':from_time',$from_time)
        ->bindValue(':to_time',$to_time)
        ->queryOne();
    }
    public static function getDuplicates()
    {
        $sql='SELECT COUNT(mpesa_payment_id) AS total,mpesa_payment_id FROM transaction_histories  GROUP BY mpesa_payment_id HAVING(total > 1)';
        return Yii::$app->analytics_db->createCommand($sql)
        ->queryAll();
    }
    public static function getUniquePlayers($start_date,$end_date)
    {
        $sql="SELECT a.reference_name, a.reference_phone, b.name 
        FROM transaction_histories a 
        LEFT JOIN stations b ON a.station_id = b.id 
        WHERE a.created_at >= :start_date AND a.created_at <= :end_date 
        GROUP BY a.reference_name, a.reference_phone, b.name";

         return Yii::$app->analytics_db->createCommand($sql)
         ->bindValue(':start_date', $start_date)
         ->bindValue(':end_date', $end_date)
         ->queryAll();
    }
    public static function removeDups($unique_field,$limits)
    {
        $sql='DELETE FROM transaction_histories WHERE mpesa_payment_id=:mpesa_payment_id LIMIT :limits';
        Yii::$app->analytics_db->createCommand($sql)
        ->bindValue(':mpesa_payment_id',$unique_field)
        ->bindValue(':limits',$limits)
        ->execute();
    }
    public static function countEntry($phone_number)
    {
        return MpesaPayments::find()->where("MSISDN='$phone_number'")->count();
    }
    public static function generateEntryNumber($phone_number,$entry_count)
    {
        return rand();
    }
    /**
     * Method to get losers lists
     * @param int $limit
     */
    public static function getLosersList($limit){
        //echo $limit;exit;
        $sql = "SELECT DISTINCT q1.reference_phone,q1.station_id,q1.reference_name,q2.plays 
            FROM transaction_histories q1
            JOIN 
            (SELECT t.reference_phone,count(t.reference_phone) AS plays
            FROM transaction_histories t
            LEFT JOIN winning_histories w 
            ON t.reference_phone = w.reference_phone
            WHERE w.id IS NULL
            GROUP BY t.reference_phone) q2
            ON q1.reference_phone = q2.reference_phone
            ORDER BY q2.plays DESC
            LIMIT $limit";
        return Yii::$app->analytics_db->createCommand($sql)
        ->queryAll();
    }
    /**
     * 
     * @param type $limit
     * @param type $amount
     */
    public static function processLosersDisbursements($response,$amount){
        //delete today winners
        $today_winners=WinningHistories::getTodayWins();
        $today_winners=explode(",",$today_winners);
        Loser::deleteAll(['in','reference_phone',$today_winners]);
        for($i=0;$i< count($response); $i++){
            try {
                $winnersmodel = new WinningHistories();
                $winnersmodel->id=Uuid::generate()->string;
                $winnersmodel->reference_name = $response[$i]->reference_name;
                $winnersmodel->reference_phone = $response[$i]->reference_phone;
                $winnersmodel->reference_code = 'adminwin';
                $winnersmodel->amount = $amount;
                $winnersmodel->created_at = date('Y-m-d H:i:s');
                $winnersmodel->unique_field=date("Ymd")."#".$response[$i]->reference_phone;
                if($winnersmodel->save(FALSE)){
                    $disbursementmodel = new Disbursements();
                    $disbursementmodel->id=Uuid::generate()->string;
                    $disbursementmodel->reference_id = $winnersmodel->id;
                    $disbursementmodel->reference_name = $response[$i]->reference_name;
                    $disbursementmodel->phone_number = $response[$i]->reference_phone;
                    $disbursementmodel->amount = $amount;
                    $disbursementmodel-> disbursement_type = 'adminwin';
                    $disbursementmodel->created_at = date('Y-m-d H:i:s');
                    $disbursementmodel->unique_field=date("Ymd")."#".$response[$i]->reference_phone;
                    $disbursementmodel->save(FALSE);
                    $telco = Myhelper::getOperator($disbursementmodel->phone_number);
                    Yii::$app->queue->push(new DisburseJob(['id'=>$disbursementmodel->id,'telco' => $telco]));
                    $response[$i]->delete(false);
                    $arr=['amount'=>$amount];
                    Myhelper::setSms('rewardPlayer',$disbursementmodel->phone_number,$arr,SENDER_NAME,$response[$i]->station_id);
                }
            }catch(IntegrityException $e){
                //allow execution
            }
        }
        
    }
    public static function processPayment($id)
    {
        $row=MpesaPayments::findOne($id);
        if (gethostname()==COMP21_NET && strlen($row->BillRefNumber)==1 && strtolower($row->BillRefNumber)=='j') {
            $station_show=StationShows::getStationShowNet($row->BillRefNumber);
        }
        else
        {
            $station_show=StationShows::getStationShow($row->BillRefNumber,date("H:i:s",strtotime($row->created_at)));
        }
        if($station_show!=NULL)
            {
                try 
                {
                    $model=new ArchivedTransactionHistories();
                    $model->id=Uuid::generate()->string;
                    $model->mpesa_payment_id=$row->id;
                    $model->reference_name=$row->FirstName." ".$row->MiddleName." ".$row->LastName;
                    $model->reference_phone=$row->MSISDN;
                    $model->reference_code=$row->BillRefNumber;
                    $model->station_id=$station_show['station_id'];
                    $model->station_show_id=$station_show['show_id'];
                    $model->amount=$row->TransAmount;
                    $model->created_at=$row->created_at;
                    $model->save(false);
                    $row->operator=Myhelper::getOperator($row->MSISDN);
                    $row->state=1;
                    $row->station_id=$station_show['station_id'];
                    $row->save(false);
                    if(in_array(gethostname(),COTZ))
                    {
                        //$totalEntry=ArchivedTransactionHistories::countEntry($row->MSISDN);
                        $totalEntry=Customer::customerTicket($row->MSISDN);
                        $entryNumber=ArchivedTransactionHistories::generateEntryNumber($row->MSISDN,$totalEntry);
                        Myhelper::setSms('validDrawEntry',$row->MSISDN,['Habari',$entryNumber,$totalEntry],SENDER_NAME,$station_show['station_id']);
                    }
                    else
                    {
                        Myhelper::setSms('validDraw',$row->MSISDN,[$row->FirstName],SENDER_NAME,$station_show['station_id']);
                    }
                    $row->operator=Myhelper::getOperator($row->MSISDN);
                    $row->state=1;
                    $row->save(false);

                }
                catch (IntegrityException $e) {
                    //allow execution
                    //var_dump($e);
                }
                
            }
            else
            {
                    $row->operator=Myhelper::getOperator($row->MSISDN);
                    $row->state=1;
                    $row->save(false);
                if(in_array(gethostname(),COTZ))
                        {
                            $totalEntry=Customer::customerTicket($row->MSISDN);
                            $entryNumber=ArchivedTransactionHistories::generateEntryNumber($row->MSISDN,$totalEntry);
                            Myhelper::setSms('validDrawEntry',$row->MSISDN,['Habari',$entryNumber,$totalEntry],SENDER_NAME,NULL);
                        }
                        else
                        {
                            Myhelper::setSms('validDraw',$row->MSISDN,[$row->FirstName],SENDER_NAME,NULL);
                        }
            }
    }
}
