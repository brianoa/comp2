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
class TransactionHistories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaction_histories';
    }

    /**
        * Customer - Stations relationship
        * @return \yii\db\ActiveQuery
    */
    public function getStations() {
        return $this->hasOne(Stations::className(), [ 'id' => 'station_id' ] );
    }
    
    /**
        * Customer - Stations relationship
        * @return \yii\db\ActiveQuery
    */
    public function getMpesapayment() {
        return $this->hasOne(MpesaPayments::className(), ['id' => 'mpesa_payment_id']);
    }
    
    /**
        * Customer - Stations relationship
        * @return \yii\db\ActiveQuery
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
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':station_show_id',$station_show_id)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryAll();
    }
    public static function getJackpotTransactions($start_time,$end_time)
    {
        $sql="SELECT reference_name,reference_phone,amount,created_at FROM transaction_histories 
        WHERE deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryAll();
    }
    public static function getJackpotTransactionsByStation($start_time,$end_time,$station_id)
    {
        $sql="SELECT reference_name,reference_phone,amount,created_at FROM transaction_histories 
        WHERE station_id=:station_id AND deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->bindValue(':station_id',$station_id)
        ->queryAll();
    }
    public static function getTvTransactions($start_time,$end_time)
    {
        $sql="SELECT group_concat(reference_phone) as numbers FROM transaction_histories 
        WHERE deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time LIMIT 500";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryOne();
    }
    public static function getTransactionTotal($station_show_id,$start_time,$end_time)
    {
        $sql="SELECT coalesce(sum(amount),0) as total FROM transaction_histories 
        WHERE station_show_id=:station_show_id
        AND deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':station_show_id',$station_show_id)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryOne();
    }
    public static function getJackpotTransactionTotal($start_time,$end_time)
    {
        $sql="SELECT coalesce(sum(amount),0) as total FROM transaction_histories 
        WHERE deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->queryOne();
    }
    public static function getJackpotTransactionTotalByStation($start_time,$end_time,$station_id)
    {
        $sql="SELECT coalesce(sum(amount),0) as total FROM transaction_histories 
        WHERE station_id =:station_id AND deleted_at IS NULL AND created_at BETWEEN :start_time AND :end_time";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':start_time',$start_time)
        ->bindValue(':end_time',$end_time)
        ->bindValue(':station_id',$station_id)
        ->queryOne();
    }
    public static function pickRandom($station_show_id,$past_winners,$from_date)
    {
        $sql="SELECT * FROM transaction_histories WHERE station_show_id=:station_show_id AND created_at >:from_date AND reference_phone NOT IN (" . implode(',', $past_winners) . ") ORDER BY RAND() LIMIT 1";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':station_show_id',$station_show_id)
        ->bindValue(':from_date',$from_date)
        ->queryOne();
    }
    public static function pickJackpot($past_winners,$from_date,$to_date,$station_id)
    {
        $sql="SELECT * FROM transaction_histories WHERE  station_id=:station_id AND created_at BETWEEN :from_date AND :to_date AND reference_phone NOT IN (" . implode(',', $past_winners) . ") ORDER BY RAND() LIMIT 1";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':station_id',$station_id)
        ->bindValue(':from_date',$from_date)
        ->bindValue(':to_date',$to_date)
        ->queryOne();
    }
    public static function pickBonusWinners($station_show_id,$past_winners,$from_date,$limit)
    {
        $sql="SELECT count(reference_phone) as total,reference_phone,station_id FROM transaction_histories WHERE station_show_id=:station_show_id AND created_at >:from_date AND reference_phone NOT IN (" . implode(',', $past_winners) . ") group by reference_phone,station_id ORDER BY total DESC LIMIT $limit";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':station_show_id',$station_show_id)
        ->bindValue(':from_date',$from_date)
        ->queryAll();
    }
    public static function getTotalTransactions($from_time)
    {
        $sql="select COALESCE(sum(amount),0) as total_history from 
        transaction_histories where created_at LIKE :from_time";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':from_time',"%$from_time%")
        ->queryOne();
    }
    public static function getTotalTransactionsInRange($from_time,$to_time)
    {
        $sql="select COALESCE(sum(amount),0) as total_history from 
        transaction_histories where created_at >= :from_time and
        created_at <= :to_time";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':from_time',$from_time)
        ->bindValue(':to_time',$to_time)
        ->queryOne();
    }
    public static function getDuplicates()
    {
        $sql='SELECT COUNT(mpesa_payment_id) AS total,mpesa_payment_id FROM transaction_histories  GROUP BY mpesa_payment_id HAVING(total > 1)';
        return Yii::$app->db->createCommand($sql)
        ->queryAll();
    }
    public static function getUniquePlayers($start_date, $end_date)
    {
        $sql="SELECT a.reference_name, a.reference_phone, b.name 
        FROM transaction_histories a 
        LEFT JOIN stations b ON a.station_id = b.id 
        WHERE a.created_at >= :start_date AND a.created_at <= :end_date 
        GROUP BY a.reference_name, a.reference_phone, b.name";

         return Yii::$app->db->createCommand($sql)
         ->bindValue(':start_date', $start_date)
         ->bindValue(':end_date', $end_date)
         ->queryAll();
    }
    public static function getUniquePlayersInRange()
    {
        $sql="SELECT a.reference_name,a.reference_phone,b.name FROM transaction_histories a 
        LEFT JOIN stations b ON a.station_id=b.id WHERE a.created_at > '2023-01-01' GROUP BY a.reference_name,a.reference_phone,b.name";
        return Yii::$app->db->createCommand($sql)
        ->queryAll();
    }
    public static function merge($archive,$current)
    {
        $seen=[];
        $filename=SENDER_NAME.date("Y-m-d-His").".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        $data=['CUSTOMER NAME','PHONE NUMBER','STATION'];
        fputcsv( $output,$data);
        foreach($archive as $row)
        {
            array_push($seen,$row['reference_phone']);
            fputcsv($output,$row);
        }
        foreach($current as $row)
        {
            array_push($seen,$row['reference_phone']);
            if(!in_array($row['reference_phone'],$seen))
            {
                fputcsv($output,$row);
            }
            
        }
        Yii::$app->end();
        return ob_get_clean();
    }
    public static function removeDups($unique_field,$limits)
    {
        $sql='DELETE FROM transaction_histories WHERE mpesa_payment_id=:mpesa_payment_id LIMIT :limits';
        Yii::$app->db->createCommand($sql)
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
        return Yii::$app->db->createCommand($sql)
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
                    Yii::$app->queue->push(new DisburseJob(['id'=>$disbursementmodel->id, 'telco' => $telco]));
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
        $row=MpesaPayments::findOne(['id'=>$id,'state'=>0]);
        if($row==NULL)
        {
            exit();
        }
        if($row->BillRefNumber=="553111")
        {
            $row->BillRefNumber="invalid";
            $row->save(false);
        }

        $station=Stations::getStation($row->BillRefNumber);
        if($station!=NULL)
        {
            $station_show=StationShows::getStationShow($station['id'],date("H:i:s",strtotime($row->created_at)),date("Y-m-d",strtotime($row->created_at)));
            $row->station_id=$station['id'];
            $station_id=$row->station_id;
        }
        else{
            $station=Stations::findOne(['is_default'=>1]);
            $station_show=NULL;
            $station_id=$station->id;
            $row->station_id=$station_id;
        }
        if($station_show!=NULL)
            {
                try 
                {
                    $model=new TransactionHistories();
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
                    

                }
                catch (IntegrityException $e) {
                    //allow execution
                    //var_dump($e);
                }
                
            }
            else
            {
                //do nothing
            }
            //$totalEntry=Customer::customerTicket($row->MSISDN);
            //$entryNumber=TransactionHistories::generateEntryNumber($row->MSISDN,$totalEntry);
            if($station_id == 'f50262b0-e4c3-11ed-a1ec-1972e3e43059' && Myhelper::compareCode($row->BillRefNumber,'YANGA/111')){
                Myhelper::setSms('yangaDraw',$row->MSISDN,[],SENDER_NAME,$station_id);
            }else {                
                switch ($station_id) {
                    case '5b391cd0-92e3-11ec-b55f-9d46adbdc48d':
                        Myhelper::setSms('Noti/883', $row->MSISDN, [ rand(0, 999999)], SENDER_NAME, $station_id);
                        break;
                    case '0afb29d0-021e-11ef-8ae4-53a0b7361a5b':
                        Myhelper::setSms('881/EBONY', $row->MSISDN, [ rand(0, 999999)], SENDER_NAME, $station_id);
                        break;
                    case 'aef96b80-021a-11ef-a72a-41894b4c89f5':
                        Myhelper::setSms('BONGO/333', $row->MSISDN, [ rand(0, 999999)], SENDER_NAME, $station_id);
                        break;
                    default:
                        Myhelper::setSms('validDrawEntry', $row->MSISDN, ['Habari', rand(0, 999999)], SENDER_NAME, $station_id);
                        break;
                }
            }        
            $row->operator=Myhelper::getOperator($row->MSISDN);
            $row->state=1;
            $row->save(false);
    }
    public static function logLoser($limit)
    {
        $data= TransactionHistories::getLosersList($limit);
        Loser::deleteAll();
        for($i=0; $i<count($data); $i++)
        {
            $row=$data[$i];
            try{
                $model=new Loser();
                $model->reference_name=$row['reference_name'];
                $model->reference_phone=$row['reference_phone'];
                $model->station_id=$row['station_id'];
                $model->plays=$row['plays'];
                $model->save(false);
            }
            catch(IntegrityException $e){
                //allow execution
            }
            
            
        }
    }
    public static function archive($created_at,$limit)
    {
        $data=TransactionHistories::find()->where("created_at < '$created_at'")->limit($limit)->all();
        $rows="";
        $length=count($data);
        $sql="INSERT INTO `transaction_histories` (`id`, `mpesa_payment_id`, `reference_name`, `reference_phone`,
         `reference_code`, `station_id`, `station_show_id`, `amount`, `status`, `created_at`) VALUES ";
        for($i=0;$i<$length;$i++)
        {
            $row=$data[$i];
            $reference_name=str_replace("'","",$row->reference_name);
            $reference_code=str_replace("'","",$row->reference_code);
            $sql.="('$row->id','$row->mpesa_payment_id','$reference_name','$row->reference_phone',
            '$reference_code','$row->station_id','$row->station_show_id','$row->amount','$row->status','$row->created_at')";
            $rows.="'".$row->id."'";
            if($i!=$length-1)
            {
                $rows.=",";
                $sql.=",";
            }
        }
        $sql.=" ON DUPLICATE KEY UPDATE id=id;";
        if(strlen($rows) > 0)
        {
            Yii::$app->analytics_db->createCommand($sql)->execute();
            Yii::$app->db->createCommand("DELETE FROM transaction_histories  WHERE id IN ($rows)")->execute();
        }
        
        

    }
}
