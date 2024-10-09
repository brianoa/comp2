<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "station_shows".
 *
 * @property string $id
 * @property string $station_id
 * @property string $name
 * @property string|null $description
 * @property string $show_code
 * @property float $amount
 * @property float $commission
 * @property float $management_commission
 * @property float $price_amount
 * @property float $target
 * @property int $draw_count
 * @property float $invalid_percentage
 * @property int $monday
 * @property int $tuesday
 * @property int $wednesday
 * @property int $thursday
 * @property int $friday
 * @property int $saturday
 * @property int $sunday
 * @property string|null $start_time
 * @property string|null $end_time
 * @property int $enabled
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class StationShows extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'station_shows';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'station_id', 'name', 'show_code', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'enabled'], 'required'],
            [['description'], 'string'],
            [['target', 'invalid_percentage', 'jackpot'], 'number'],
            [['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'enabled'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['station_id', 'name', 'show_code', 'start_time', 'end_time'], 'string', 'max' => 255],
            [['show_code'], 'unique'],
            [['id'], 'unique'],
        ];
    }

    /**
     *   Stations relationship
     * @return \yii\db\ActiveQuery
     */
    public function getStations()
    {
        return $this->hasOne(Stations::className(), ['id' => 'station_id']);
    }
    public static function getStationShows()
    {
        $arr   = [];

        if (\Yii::$app->myhelper->isStationManager()) {
            $stations = implode(",", array_map(function ($string) {
                return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $model = StationShows::find()->where("station_id IN ($stations)")->orderBy("name ASC")->all();
        } else {
            $model = StationShows::find()->orderBy("name ASC")->all();
        }
        foreach ($model as $value) {
            $arr[$value->id] = $value->name;
        }
        return $arr;
    }
    public static function getNonJackpotShows()
    {
        $query = self::find()->where(['jackpot' => 0])->orderBy("name ASC")->all();
        return ArrayHelper::map($query, 'id', 'name');
    }
    public static function getJackpotShows()
    {
        $arr   = [];
        $model = StationShows::find()->where("jackpot=1")->orderBy("name ASC")->all();
        foreach ($model as $value) {
            $arr[$value->id] = $value->name;
        }
        return $arr;
    }
    public static function convertToMinutes($timeStr)
    {
        list($hours, $minutes) = explode(":", $timeStr);
        return $hours * 60 + $minutes;
    }
    public static function isConflict($new_show, $shows, $day)
    {
        $new_start = self::convertToMinutes($new_show["start_time"]);
        $new_end = self::convertToMinutes($new_show["end_time"]);

        foreach ($shows as $show) {
            if ($show["days"][$day] == 1) {
                $existing_start = self::convertToMinutes($show["start_time"]);
                $existing_end = self::convertToMinutes($show["end_time"]);

                if ($new_start < $existing_end && $new_end > $existing_start) {
                    return true;
                }
            }
        }
        return false;
    }
    public static function getshows($id)
    {
        $shows = StationShows::find()->where(["station_id" => $id])->all();
        $shows_arr = [];
        if ($shows !== NULL) {
            foreach ($shows as $show) {
                $shows_arr[] = [
                    "start_time" => $show->start_time,
                    "end_time" => $show->end_time,
                    "days" => [
                        "monday" => $show->monday,
                        "tuesday" => $show->tuesday,
                        "wednesday" => $show->wednesday,
                        "thursday" => $show->thursday,
                        "friday" => $show->friday,
                        "saturday" => $show->saturday,
                        "sunday" => $show->sunday
                    ]
                ];
            }
        }
        return $shows_arr;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'station_id' => 'Station ID',
            'name' => 'Name',
            'description' => 'Description',
            'show_code' => 'Show Code',
            'target' => 'Target',
            'invalid_percentage' => 'Invalid Percentage',
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
            'start_time' => 'Start Time(HH:mm)',
            'end_time' => 'End Time(HH:mm)',
            'enabled' => 'Enabled',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
    public static function getStationShow($station_id, $time, $date)
    {
        $current_day = strtolower(date("l", strtotime($date)));
        $sql = "SELECT b.station_id,b.id AS show_id,b.start_time,b.end_time FROM  station_shows b  WHERE b.station_id=:station_id AND b.enabled=1 AND b.deleted_at IS NULL  
        AND b." . $current_day . "=1  AND start_time <='$time'  AND end_time >='$time'";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':station_id', $station_id)
            ->queryOne();
    }
    public static function getStationShowSummary($start_date, $end_date)
    {
        $sql = "SELECT a.id,a.station_id,a.name AS station_show_name,b.name AS station_name,
        COALESCE((SELECT SUM(amount) FROM transaction_histories WHERE station_show_id=a.id AND deleted_at IS NULL AND created_at BETWEEN :start_date AND :end_date),0) AS total_revenue,
        COALESCE((SELECT SUM(amount) FROM commissions WHERE station_show_id=a.id AND deleted_at IS NULL AND created_at BETWEEN :start_date AND :end_date),0) AS total_commission,
        COALESCE((SELECT SUM(amount) FROM winning_histories WHERE station_show_id=a.id AND deleted_at IS NULL AND created_at BETWEEN :start_date AND :end_date),0) AS total_payout
         FROM station_shows a LEFT JOIN stations b ON a.station_id=b.id 
         WHERE a.deleted_at IS NULL AND a.enabled=1 ORDER BY total_revenue DESC";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':start_date', $start_date)
            ->bindValue(':end_date', $end_date)
            ->queryAll();
    }
    public static function getPayout($start_date, $end_date)
    {
        $sql = "SELECT a.id,a.station_id,a.name AS station_show_name,b.name AS station_name,
        COALESCE((SELECT SUM(amount) FROM winning_histories WHERE station_show_id=a.id AND deleted_at IS NULL AND created_at BETWEEN :start_date AND :end_date),0) AS total_payout
         FROM station_shows a LEFT JOIN stations b ON a.station_id=b.id 
         WHERE a.deleted_at IS NULL AND a.enabled=1 ";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':start_date', $start_date)
            ->bindValue(':end_date', $end_date)
            ->queryAll();
    }
    public static function getShowForCommission($current_day)
    {
        $sql = "SELECT * FROM station_shows WHERE " . $current_day . "=1 AND enabled=1 AND deleted_at IS NULL AND end_time < CURRENT_TIME()";
        return Yii::$app->db->createCommand($sql)
            ->queryAll();
    }
    public static function getMidnightShow($current_day)
    {
        $sql = "SELECT * FROM station_shows WHERE " . $current_day . "=1 AND enabled=1 AND deleted_at IS NULL AND end_time > '23:30'  AND end_time <= '23:59'";
        return Yii::$app->db->createCommand($sql)
            ->queryAll();
    }
}
