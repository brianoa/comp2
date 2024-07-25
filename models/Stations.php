<?php

namespace app\models;
use app\models\MpesaPayments;

use Yii;

/**
 * This is the model class for table "stations".
 *
 * @property string $id
 * @property string $name
 * @property string|null $address
 * @property int $enabled
 * @property string $station_code
 * @property float $invalid_percentage
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class Stations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'station_code','frequency'], 'required'],
            [['enabled','frequency','is_default'], 'integer'],
            [['invalid_percentage'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['name', 'address'], 'string', 'max' => 255],
            [['station_code'], 'string', 'max' => 100],
            [['station_code'], 'unique'],
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
            'name' => 'Name',
            'address' => 'Address',
            'enabled' => 'Enabled',
            'station_code' => 'Station Code',
            'invalid_percentage' => 'Invalid Percentage',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'frequency' => 'Frequency'
        ];
    }
    /**
     * Method to getPermission group list
     * @return type
     */
    public static function getStations(){
        $list = [];
        if(\Yii::$app->myhelper->isStationManager()){
           $records =  Stations::find()->where(['IN','id', \Yii::$app->myhelper->getStations()])->andWhere(['enabled'=>1])->all();
        }else{
            $records = Stations::findAll(['enabled'=>1]);
        }
        foreach ($records as $record) {
            $list[$record -> id] = $record->name;
        }
        return $list;
    }
    public static function getFilterstations()
{
    if (\Yii::$app->myhelper->isStationManager()) {
        $records = Stations::find()
            ->where(['IN', 'id', \Yii::$app->myhelper->getStations()])
            ->andWhere(['enabled' => 1])
            ->all();
    } else {
        $records = Stations::findAll(['enabled' => 1]);
    }

    return $records;
}
    public static function getStationResult($from_time)
    {
        $response=array();
        $sql="select a.id,a.id as station_id,a.name as station_name,a.name,a.station_code from stations a where a.deleted_at IS NULL order by a.name asc";
        $data= Yii::$app->db->createCommand($sql)
        ->bindValue(':from_time',"%$from_time%")
        ->queryAll();
        for($i=0;$i<count($data); $i++)
        {
            $row=$data[$i];
            $row['amount']=MpesaPayments::getStationTotalMpesa($from_time,$row['station_code'])['amount'];
            array_push($response,$row);

        }
        return $response;
    }
    public static function getStationTotalResult($start_period,$end_period)
    {
        $sql="select a.id,a.id as station_id,a.name as station_name,a.station_code,
        COALESCE((select sum(b.TransAmount) from mpesa_payments b where b.deleted_at IS NULL AND b.created_at >= :start_period and
        b.created_at <= :end_period AND  (SUBSTRING(b.BillRefNumber,1,3)=SUBSTRING(a.station_code,1,3) || RIGHT(b.BillRefNumber,3)=RIGHT(a.station_code,3) || b.BillRefNumber LIKE concat('%',a.station_code,'%'))),0) 
        as amount from stations a where a.deleted_at IS NULL order by a.name asc";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':start_period',$start_period)
        ->bindValue(':end_period',$end_period)
        ->queryAll();
    }
    public static function getDayStationTotalResult($the_day)
    {
        $sql="select a.id,a.id as station_id,a.name as station_name,a.station_code,
        COALESCE((select sum(b.TransAmount) from mpesa_payments b where b.deleted_at IS NULL AND b.created_at LIKE :the_day  AND  (SUBSTRING(b.BillRefNumber,1,3)=SUBSTRING(a.station_code,1,3) || RIGHT(b.BillRefNumber,3)=RIGHT(a.station_code,3) || b.BillRefNumber LIKE concat('%',a.station_code,'%'))),0) as amount from stations a where a.deleted_at IS NULL order by a.name asc";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':the_day',"%$the_day%")
        ->queryAll();
    }
    public static function getStation($stat_code)
    {
        $scode=$stat_code;
        $sql="SELECT a.id,a.name,a.station_code FROM stations a 
        WHERE a.deleted_at IS NULL AND  (SUBSTRING(a.station_code,1,3)=SUBSTRING(:stat_code,1,3) || 
        RIGHT(a.station_code,3)=RIGHT(:stat_code,3) || a.station_code LIKE :scode) ";
        return Yii::$app->db->createCommand($sql)
        ->bindValue(':stat_code',$stat_code)
        ->bindValue(':scode',"%$scode%")
        ->queryOne();
    }
    public static function getActiveStations()
    {
        if(\Yii::$app->myhelper->isStationManager()){
           return Stations::find()->where(['IN','id', \Yii::$app->myhelper->getStations()])->andWhere("deleted_at is null")->orderBy("name asc")->all();
        }else{
            return Stations::find()->where("deleted_at is null")->orderBy("name asc")->all();
        }
        
    }
}
