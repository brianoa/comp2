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
    public static function getRevenueReport($start_date, $end_date, $station = null)
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
    private static function convertYearMonthToMonthYear($yearMonth)
    {
        $months = [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Aug',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        ];

        list($year, $month) = explode('-', $yearMonth);

        return isset($months[$month]) ? $months[$month] . ' ' . $year : null;
    }
    public static function getMonthlyRevenues()
    {
        // Set the start and end date
        $startDate = (new \DateTime())->modify('first day of -5 months')->setTime(0, 0);
        $endDate = (new \DateTime())->modify('last day of this month')->setTime(23, 59, 59);

        // Build the query
        $sql = RevenueReport::find()
            ->select(['DATE_FORMAT(revenue_date, "%Y-%m") as month_year', 'SUM(total_revenue) as total_revenue'])
            ->where(['between', 'revenue_date', $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->groupBy(['DATE_FORMAT(revenue_date, "%Y-%m")'])
            ->orderBy(['DATE_FORMAT(revenue_date, "%Y-%m")' => SORT_ASC]);

        if (\Yii::$app->myhelper->isStationManager()) {
            $stations = implode(",", array_map(function ($string) {
                return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $sql->andWhere("station_id IN ($stations)");
        }

        // Fetch monthly revenue data
        $monthlyRevenues = $sql->asArray()->all();

        // Log the fetched monthly revenues for debugging
        Yii::info($monthlyRevenues, 'debug');

        // Prepare a map for monthly revenues
        $revenueMap = [];
        foreach ($monthlyRevenues as $data) {
            // Use custom conversion method
            $monthYear = self::convertYearMonthToMonthYear($data['month_year']);
            if ($monthYear) {
                Yii::info("Raw month-year: {$data['month_year']} - Formatted month-year: {$monthYear}", 'debug');
                $revenueMap[$monthYear] = $data['total_revenue'];
            }
        }

        // Prepare the results for the last 6 months
        $result = [];
        $currentDate = new \DateTime();
        $currentDate->modify('first day of this month'); // Start from the first day of the current month
        for ($i = 0; $i < 6; $i++) {
            $monthYear = $currentDate->format('M Y');
            $result[] = [
                'month_year' => $monthYear,
                'total_revenue' => isset($revenueMap[$monthYear]) ? $revenueMap[$monthYear] : 0  // Default to 0 if not set
            ];
            $currentDate->modify('-1 month');
        }

        // Reverse the results to start from the oldest month
        $result = array_reverse($result);

        // Log the final result for debugging
        Yii::info($result, 'debug-final-result');

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
