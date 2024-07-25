<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "revenue_report".
 *
 * @property int $id
 * @property string|null $revenue_date
 * @property int|null $total_revenue
 * @property int|null $total_awarded
 * @property int|null $net_revenue
 */
class RevenueReport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'revenue_report';
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
    public function rules()
    {
        return [
            [['revenue_date', 'station_id', 'unique_field', 'station_name'], 'safe'],
            [['total_revenue', 'total_awarded', 'net_revenue'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'station_name' => 'Station',
            'revenue_date' => 'Revenue Date',
            'total_revenue' => 'Total Revenue',
            'total_awarded' => 'Total Awarded',
            'net_revenue' => 'Net Revenue',
        ];
    }
    public static function getRevenueReport($start_date, $end_date,$station = null)
    {
        $sql = RevenueReport::find()->where("revenue_date >= '$start_date'")->andWhere("revenue_date <='$end_date'");
        if (\Yii::$app->myhelper->isStationManager()) {
            $stations = implode(",", array_map(function ($string) {
                return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $sql->andWhere("station_id IN ($stations)");
        }
        if ($station) {
            $sql->andWhere(['station_id' => $station]);
        }
    
        return $sql->all();
    }
    public static function getDailyRevenues()
    {
        // Prepare the last 7 days' dates
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $dates[] = date('Y-m-d', strtotime("-$i days"));
        }

        $sql = RevenueReport::find()
            ->select(['revenue_date', 'SUM(total_revenue) as total_revenue'])
            ->where(['in', 'revenue_date', $dates])
            ->groupBy(['revenue_date'])
            ->orderBy(['revenue_date' => SORT_ASC]);
        if (\Yii::$app->myhelper->isStationManager()) {
            $stations = implode(",", array_map(function ($string) {
                return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $sql->andWhere("station_id IN ($stations)");
        }
        // Fetch revenue data
        $dailyRevenues = $sql
            ->asArray()
            ->all();

        // $dailyRevenues = RevenueReport::find()
        //         ->select(['DATE_FORMAT(revenue_date, "%b %d") as day', 'SUM(total_revenue) as total_revenue'])
        //         ->where(['between', 'revenue_date', $startDateFormatted, $endDateFormatted])
        //         ->groupBy(['day'])
        //         ->orderBy(['revenue_date' => SORT_ASC])
        //         ->asArray()
        //         ->all();
        // Map results to a date-to-revenue array
        $revenueMap = [];
        foreach ($dailyRevenues as $revenue) {
            $revenueMap[$revenue['revenue_date']] = $revenue['total_revenue'];
        }

        // Prepare result with zero values for days with no revenue
        $result = [];
        foreach ($dates as $date) {
            $result[] = [
                'day' => date('M d', strtotime($date)), // Format to "MMM dd"
                'total_revenue' => isset($revenueMap[$date]) ? $revenueMap[$date] : 0 // Default to 0 if not set
            ];
        }
        // print_r($result);
        // exit;
        return $result;
    }
    public static function getMonthlyRevenues()
    {
        $startDate = new \DateTime();
        $startDate->modify('-6 months');
        $endDate = new \DateTime();

        $sql = RevenueReport::find()
            ->select(['DATE_FORMAT(revenue_date, "%b %Y") as month_year', 'SUM(total_revenue) as total_revenue'])
            ->where(['between', 'revenue_date', $startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy(['month_year'])
            ->orderBy(['month_year' => SORT_ASC]);

        if (\Yii::$app->myhelper->isStationManager()) {
            $stations = implode(",", array_map(function ($string) {
                return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $sql->andWhere("station_id IN ($stations)");
        }

        // Fetch monthly revenue data
        $monthlyRevenues = $sql
            ->asArray()
            ->all();

        // Prepare a map for monthly revenues
        $revenueMap = [];
        foreach ($monthlyRevenues as $data) {
            $revenueMap[$data['month_year']] = $data['total_revenue'];
        }

        // Prepare the results for the last 6 months
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthYear = date('M Y', strtotime("-$i month")); // Get last 6 months with year
            $result[] = [
                'month_year' => $monthYear,
                'total_revenue' => isset($revenueMap[$monthYear]) ? $revenueMap[$monthYear] : 0  // Default to 0 if not set
            ];
        }
        return $result;
    }

    public static function checkDuplicate($unique_field)
    {
        return RevenueReport::find()->where("unique_field='$unique_field'")->one();
    }
    /**
     * Method to get monthly growth trend
     * @return type
     */
    public static function monthlyGrowthTrendData()
    {
        $sum = [];
        $range = [];
        $year = date('Y');
        for ($i = 1; $i <= 12; $i++) {
            if (\Yii::$app->myhelper->isStationManager()) {
                $stations = implode(",", array_map(function ($string) {
                    return '"' . $string . '"';
                }, \Yii::$app->myhelper->getStations()));
                $data = RevenueReport::find()
                    ->select(['total' => 'SUM(total_revenue)'])
                    ->where("MONTH(revenue_date)='$i'")
                    ->andWhere("YEAR(revenue_date)='$year'")
                    ->andWhere("station_id IN ($stations)")
                    ->createCommand()->queryAll();
            } else {
                $data = RevenueReport::find()
                    ->select(['total' => 'SUM(total_revenue)'])
                    ->where("MONTH(revenue_date)='$i'")
                    ->andWhere("YEAR(revenue_date)='$year'")
                    ->createCommand()->queryAll();
            }
            $sum[] = $data[0]['total'];
            $range[] = $i;
        }
        return  ['sum' => $sum, 'range' => $range];
    }
    /**
     * 
     * @param type $start_date
     * @param type $end_date
     * @return type
     */
    public static function rangeGrowthTrendData($start_date, $end_date)
    {
        $sum = [];
        $range = [];
        $year = date('Y');

        for ($i = $start_date; $i <= $end_date; $i = date('Y-m-d', strtotime('+1 day', strtotime($i)))) {

            if (\Yii::$app->myhelper->isStationManager()) {
                $stations = implode(",", array_map(function ($string) {
                    return '"' . $string . '"';
                }, \Yii::$app->myhelper->getStations()));
                $data = RevenueReport::find()
                    ->select(['total' => 'SUM(total_revenue)'])
                    ->where("revenue_date ='$i'")
                    ->andWhere("station_id IN ($stations)")
                    ->createCommand()->queryAll();
            } else {
                $data = RevenueReport::find()
                    ->select(['total' => 'SUM(total_revenue)'])
                    ->where("revenue_date ='$i'")
                    ->createCommand()->queryAll();
            }
            $sum[] = $data[0]['total'] > 0 ? $data[0]['total'] : 0;
            $range[] = $i;
        }

        return  ['sum' => $sum, 'range' => $range];
    }
}
