<?php

namespace app\models;

use Yii;
use app\models\StationShows;

/**
 * This is the model class for table "show_summary".
 *
 * @property int $id
 * @property string|null $station_show_id
 * @property int|null $total_revenue
 * @property int|null $total_commission
 * @property int|null $total_payouts
 * @property string|null $report_date
 * @property string $created_at
 */
class ShowSummary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'show_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['total_revenue', 'total_commission', 'total_payouts'], 'integer'],
            [['report_date', 'created_at'], 'safe'],
            [['station_show_id','station_name','station_show_name','station_id'], 'string', 'max' => 50],
        ];
    }
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('analytics_db');
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'station_show_id' => 'Station Show ID',
            'total_revenue' => 'Total Revenue',
            'total_commission' => 'Total Commission',
            'total_payouts' => 'Total Payouts',
            'report_date' => 'Report Date',
            'created_at' => 'Created At',
        ];
    }
    public static function checkDuplicate($report_date)
    {
        return ShowSummary::find()->where("report_date=:report_date",[':report_date' => $report_date])->count();
    }
    public static function findShow($report_date,$station_show_id)
    {
        return ShowSummary::find()->where("report_date='$report_date'")->andWhere("station_show_id='$station_show_id'")->one();
    }
    public static function getShowSummary($start_date,$end_date, $station = null)
    {
         $sql="SELECT station_name,station_show_name,COALESCE(SUM(total_revenue),0) AS revenue,COALESCE(SUM(total_commission),0) AS commission,
            COALESCE(SUM(total_payouts),0) AS payout FROM show_summary WHERE ";
            if(\Yii::$app->myhelper->isStationManager()){
                $stations = implode(",", array_map(function($string) {
                   return '"' . $string . '"';
                }, \Yii::$app->myhelper->getStations()));
                $sql .=" `station_id` IN ($stations) AND ";
            }
            if ($station) {
                $sql .= " `station_id` = :station AND ";
            }
            $sql .= " report_date BETWEEN :start_date AND :end_date
            GROUP BY station_show_id, station_name, station_show_name
            ORDER BY revenue DESC";
            $command = Yii::$app->analytics_db->createCommand($sql)
            ->bindValue(':start_date', $start_date)
            ->bindValue(':end_date', $end_date);

            if ($station) {
                $command->bindValue(':station', $station);
            }
            return $command->queryAll();
        }

    public static function logShowSummary($start_date=NULL)
    {
        if(date("H")=="00")
        {
            $start_date= date('Y-m-d',strtotime('yesterday'));
        }
        else if($start_date==NULL)
        {
            $start_date= date('Y-m-d');
        }
        else
        {
            //do nothing
        }
        $end_date = date("$start_date 23:59:59");
            $data=StationShows::getStationShowSummary($start_date,$end_date);
            for($i=0;$i<count($data); $i++)
            {
                $row=$data[$i];
                $show=ShowSummary::findShow($start_date,$row['id']);
                if($show==NULL)
                {
                    $model=new ShowSummary();
                    $model->station_show_id=$row['id'];
                    $model->station_id=$row['station_id'];
                    $model->total_revenue=$row['total_revenue'];
                    $model->total_commission=$row['total_commission'];
                    $model->total_payouts=$row['total_payout'];
                    $model->report_date= $start_date;
                    $model->created_at=date("Y-m-d H:i:s");
                    $model->station_show_name=$row['station_show_name'];
                    $model->station_name=$row['station_name'];
                    $model->save();
                }
                else
                {
                    $show->total_revenue=$row['total_revenue'];
                    $show->total_commission=$row['total_commission'];
                    $show->total_payouts=$row['total_payout'];
                    $show->save();
                }
                
                
            }
        
    }
    public static function log($start_date)
    {
        $end_date = date("$start_date 23:59:59");
            $data=StationShows::getPayout($start_date,$end_date);
            for($i=0;$i<count($data); $i++)
            {
                $row=$data[$i];
                $show=ShowSummary::findShow($start_date,$row['id']);
                if($show!=NULL)
                {
                    $show->total_payouts=$row['total_payout'];
                    $show->save();
                }
            
                
            }
        
    }
    public static function updateShowSummary($start_date)
    {
        $end_date = date("$start_date 23:59:59");
            $data=StationShows::getStationShowSummary($start_date,$end_date);
            for($i=0;$i<count($data); $i++)
            {
                $row=$data[$i];
                $show=ShowSummary::findShow($start_date,$row['id']);
                if($show==NULL)
                {
                    $model=new ShowSummary();
                    $model->station_show_id=$row['id'];
                    $model->station_id=$row['station_id'];
                    $model->total_revenue=$row['total_revenue'];
                    $model->total_commission=$row['total_commission'];
                    $model->total_payouts=$row['total_payout'];
                    $model->report_date= $start_date;
                    $model->created_at=date("Y-m-d H:i:s");
                    $model->station_show_name=$row['station_show_name'];
                    $model->station_name=$row['station_name'];
                    $model->save();
                }
                else
                {
                    $show->total_revenue=$row['total_revenue'];
                    $show->total_commission=$row['total_commission'];
                    $show->total_payouts=$row['total_payout'];
                    $show->save();
                }
                
                
            }
        
    }
}
