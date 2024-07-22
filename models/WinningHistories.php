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
class WinningHistories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'winning_histories';
    }

    /**
     * Customer - Stations relationship
     * @return \yii\db\ActiveQuery
     */
    public function getStations()
    {
        return $this->hasOne(Stations::className(), ['id' => 'station_id']);
    }

    /**
     * Customer - Stations relationship
     * @return \yii\db\ActiveQuery
     */
    public function getStationshows()
    {
        return $this->hasOne(StationShows::className(), ['id' => 'station_show_id']);
    }

    /**
     * Customer - Stations relationship
     * @return \yii\db\ActiveQuery
     */
    public function getPrizes()
    {
        return $this->hasOne(Prizes::className(), ['id' => 'prize_id']);
    }

    /**
     * Customer - Stations relationship
     * @return \yii\db\ActiveQuery
     */
    public function getStationshowprize()
    {
        return $this->hasOne(StationShowPrizes::className(), ['id' => 'station_show_prize_id']);
    }
    /**
     * Customer - Stations relationship
     * @return \yii\db\ActiveQuery
     */
    public function getPresenter()
    {
        return $this->hasOne(Users::className(), ['id' => 'presenter_id']);
    }


    /**
     * Getter for users full name
     * @return string
     */
    public function getFullname()
    {
        if (isset($this->presenter->first_name) && isset($this->presenter->last_name)) {
            return $this->presenter->first_name . " " . $this->presenter->last_name;
        } elseif (isset($this->presenter->first_name)) {
            return $this->presenter->first_name;
        } elseif (isset($this->presenter->last_name)) {
            return $this->presenter->last_name;
        }
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
    public static function distinctWinners($stationId, $frequency, $startDate)
    {
        $sql = "SELECT DISTINCT(reference_phone) AS phone FROM winning_histories WHERE station_id=:stationId AND created_at > DATE_SUB(:startDate, INTERVAL :frequency DAY)";
        $data = Yii::$app->db->createCommand($sql)
            ->bindValue(':stationId', $stationId)
            ->bindValue(':frequency', $frequency)
            ->bindValue(':startDate', $startDate)
            ->queryAll();
        $arr = [];
        for ($i = 0; $i < count($data); $i++) {
            $arr[$i] = $data[$i]['phone'];
        }
        return $arr;
    }
    public static function getRecentWinners($station_show_id, $today)
    {
        $sql = "SELECT a.reference_name,a.reference_code,a.reference_phone,b.name,a.created_at FROM winning_histories a
         LEFT JOIN prizes b ON a.prize_id=b.id WHERE 
        station_show_id=:station_show_id AND a.created_at >:today";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':station_show_id', $station_show_id)
            ->bindValue(':today', $today)
            ->queryAll();
    }
    public static function getDayPayout($station_show_id, $the_day)
    {
        $sql = "SELECT COALESCE(SUM(amount),0) AS total FROM winning_histories WHERE station_show_id=:station_show_id
        AND created_at LIKE :the_day";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':station_show_id', $station_show_id)
            ->bindValue(':the_day', "%$the_day%")
            ->queryOne();
    }
    public static function getPayout($the_day)
    {
        if (\Yii::$app->myhelper->isStationManager()) {
            $stations = implode(",", array_map(function ($string) {
                return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $sql = "SELECT COALESCE(SUM(amount),0) AS total FROM winning_histories WHERE 
            created_at LIKE :the_day AND station_id IN ($stations)";
        } else {
            $sql = "SELECT COALESCE(SUM(amount),0) AS total FROM winning_histories WHERE 
         created_at LIKE :the_day";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':the_day', "%$the_day%")
            ->queryOne();
    }
    public static function getPayoutPerStation($the_day, $station_id)
    {
        $sql = "SELECT COALESCE(SUM(amount),0) AS total FROM winning_histories WHERE 
         created_at LIKE :the_day AND station_id=:station_id";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':the_day', "%$the_day%")
            ->bindValue(':station_id', $station_id)
            ->queryOne();
    }
    public static function dailyAwarding($start_date, $end_date)
    {
        $sql = 'SELECT a.prize_id,a.station_id,a.station_show_id,b.name AS station_name,c.name AS show_name,d.name AS prize_name,CONCAT(c.start_time,"-",c.end_time) AS show_timing,
        (SELECT COALESCE(SUM(amount),0)  FROM winning_histories WHERE station_show_id=a.station_show_id AND prize_id=a.prize_id AND created_at BETWEEN :start_date AND :end_date) AS awarded
        FROM winning_histories a LEFT JOIN stations b ON a.station_id=b.id LEFT JOIN station_shows c ON a.station_show_id=c.id LEFT JOIN prizes d 
        ON a.prize_id=d.id WHERE a.created_at BETWEEN :start_date AND :end_date GROUP BY a.station_id,a.station_show_id,a.prize_id ORDER BY TIME(c.start_time) ASC';
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':start_date', $start_date)
            ->bindValue(':end_date', $end_date)
            ->queryAll();
    }
    public static function logDailyAwards()
    {
        if (date("H") == "00") {
            $winning_date = date('Y-m-d', strtotime('yesterday'));
        } else {
            $winning_date = date('Y-m-d');
        }
        $start_date = $winning_date . " 00:00";
        $end_date = $winning_date . " 23:59";
        $data = WinningHistories::dailyAwarding($start_date, $end_date);
        for ($i = 0; $i < count($data); $i++) {
            $winner = $data[$i];
            $winnerLog = WinnerSummary::checkDuplicate($winner['station_show_id'] . "-" . $winner['prize_id'] . "-" . $winning_date);
            if ($winnerLog == NULL) {
                try {
                    $model = new WinnerSummary();
                    $model->station_id = $winner['station_id'];
                    $model->station_show_id = $winner['station_show_id'];
                    $model->station_name = $winner['station_name'];
                    $model->show_name = $winner['show_name'];
                    $model->prize_id = $winner['prize_id'];
                    $model->prize_name = $winner['prize_name'];
                    $model->show_timing = $winner['show_timing'];
                    $model->awarded = $winner['awarded'];
                    $model->winning_date = $winning_date;
                    $model->unique_field = $winner['station_show_id'] . "-" . $winner['prize_id'] . "-" . $winning_date;
                    $model->save(false);
                } catch (IntegrityException $e) {
                    //allow execution
                }
            } else {
                $winnerLog->awarded = $winner['awarded'];
                $winnerLog->save(false);
            }
        }
    }
    /**
     * Method to get customer report
     * @param type $start_date
     * @param type $end_date
     * @return type
     */
    public static function getCustomerreport($start_date, $end_date)
    {
        $sql = "SELECT  DISTINCT q1.reference_phone,q1.plays ,q2.reference_name,q2.station_id,st.name
        FROM winning_histories q2 
        JOIN 
        (SELECT  reference_phone, COUNT(reference_phone) As plays
        FROM winning_histories
        WHERE created_at BETWEEN  :start_date AND :end_date
        GROUP BY reference_phone)  q1
        ON q2.reference_phone = q1.reference_phone
        JOIN stations st ON q2.station_id = st.id
        WHERE ";
        if (\Yii::$app->myhelper->isStationManager()) {
            $stations = implode(",", array_map(function ($string) {
                return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $sql .= " `station_id` IN ($stations) AND ";
        }
        $sql .= "q2.created_at BETWEEN  :start_date AND :end_date
        ORDER BY q1.plays DESC";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':start_date', $start_date)
            ->bindValue(':end_date', $end_date)
            ->queryAll();
    }
    public static function saveWin(
        $win_key,
        $prize_id,
        $reference_name,
        $reference_phone,
        $reference_code,
        $station_id,
        $station_show_id,
        $presenter_id,
        $amount,
        $unique_field
    ) {
        try {
            $model = new WinningHistories();
            $model->id = $win_key;
            $model->prize_id = $prize_id;
            $model->station_show_prize_id = $prize_id;
            $model->reference_name = $reference_name;
            $model->reference_phone = $reference_phone;
            $model->reference_code = $reference_code;
            $model->station_id = $station_id;
            $model->station_show_id = $station_show_id;
            $model->presenter_id = $presenter_id;
            $model->amount = $amount;
            $model->unique_field = $unique_field;
            $model->created_at = date("Y-m-d H:i:s");
            $model->status = 0;
            $model->save(false);
            return $model;
        } catch (IntegrityException $e) {
            var_dump($e->getMessage());
        }
    }
    public static function getTodayWins()
    {
        $sql = "SELECT GROUP_CONCAT(reference_phone) as phones FROM winning_histories WHERE created_at > " . date('Y-m-d');
        return Yii::$app->db->createCommand($sql)
            ->queryOne()['phones'];
    }
}
