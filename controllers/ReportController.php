<?php
namespace app\controllers;

use app\components\AwardsJob;
use app\components\LastHourJob;
use app\components\LogCommissionJob;
use app\components\LogLoserJob;
use Exception;
use Throwable;
use Yii;
use app\models\MpesaPayments;
use app\models\TransactionHistories;
use app\models\Stations;
use app\models\Commissions;
use app\models\CommissionSummary;
use app\models\HourlyPerformanceReports;
use app\models\WinningHistories;
use app\models\StationShows;
use app\models\ShowSummary;
use app\models\WinnerSummary;
use app\models\RevenueReport;
use app\models\Disbursements;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\db\IntegrityException;
use app\components\Myhelper;
use app\components\RevenueJob;
use app\components\ShowSummaryJob;
use app\components\SiteReportJob;
use app\models\ArchivedTransactionHistories;
use app\models\SiteReport;
use app\models\Loser;
use kartik\mpdf\Pdf;
use yii\helpers\Url;

class ReportController extends Controller{
        /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['hourlyperformance','exporthourlyperformance', 'presentercommission','dailyawarding','exportdailyawarding','revenue','revenueexport','exportcommissionsummary','commissionsummary','showsummary','exportshowsummary','customerreport','exportpayouts','loserpayout','growthtrend','playercurrent','playerarchive','station','backlog','updateshow','stake','shows','winner'],
                'rules' => [
                    [
                        'actions' => ['hourlyperformance','exporthourlyperformance','customerreport','payouts','exportpayouts','growthtrend','station','backlog','updateshow'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(34) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['presentercommission'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(35) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['dailyawarding','exportdailyawarding'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(36) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['revenue','exportrevenue'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(37) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['commissionsummary','exportcommissionsummary'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(38) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['loserpayout','playercurrent','playerarchive'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(29) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['showsummary','exportshowsummary','stake','shows','winner'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(40) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionHourlyperformance()
    {
        $model = new HourlyPerformanceReports();
        $response =  $model->hourlyReport();
        $act = new \app\models\ActivityLog();
        $act -> desc = "hourly_performance report";
        $act ->setLog();
        return $this->render('hourly_performance', [
            'response' => $response
            ]);
    }
    /**
     * Method to export hourly performance
     */
    public function actionExporthourlyperformance(){
        $filename="Hourlypeformance".date("Y-m-d-His").".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        $model = new HourlyPerformanceReports();
        $response =  $model->hourlyReport();
        fputcsv( $output, $response[0]);
        for($i=1;$i< count($response); $i++)
        {
            //print_r($response[$i]);exit();
            //print_r($row);exit();
            fputcsv( $output, $response[$i]);
        }
        Yii::$app->end();
        return ob_get_clean();
    }
    /**
     * Function to show Active customers
     * @return type
     */
    public function actionCustomerreport(){ 
        $start_date= date('Y-m-d');
        $end_date = $start_date;
        if ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'daily' ) {
            $start_date= date('Y-m-d');
            $end_date = $start_date;
            $response= WinningHistories::getCustomerreport($start_date,$end_date);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'monthly' ) {
            $start_date= date('Y-m-01');
            $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
            $end_date = date("Y-m-$d");
            $response=WinningHistories::getCustomerreport($start_date,$end_date);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'range' ) {
                if ( isset( $_GET['from'] ) && isset( $_GET['to'] ) ) {
                        $end_date       = $_GET['to'];
                        $start_date     = $_GET['from'];
                        $date1    = strtotime( $end_date);
                        $date2    = strtotime( $start_date);
                        if ( $date1 < $date2 ) {
                                Yii::$app->session->setFlash('error', 'Error: start date should be before the end date' );
                        }
                        $response=WinningHistories::getCustomerreport($start_date,$end_date);
                } else {
                    $start_date= date('Y-m-01');
                    $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
                    $end_date = date("Y-m-$d");
                    $response=WinningHistories::getCustomerreport($start_date,$end_date);
                }
        } else {
            $response=WinningHistories::getCustomerreport($start_date,$end_date);
        }
        return $this->render('customerreport', [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'response' => $response
        ]);
        
    }
    /**
        * Method to render admin payout for losers
        * @return type
    */
    public function actionLoserpayout(){
        if(isset($_POST['limit']) && $_POST['limit'] > 0 ){
            $limit = (int)$_POST['limit'];
            $loadcheck = true;
        }else{
            $loadcheck = FALSE;
            $limit = 100;
        }
        if(\Yii::$app->myhelper->isStationManager()){
            $stations = implode(",", array_map(function($string) {
               return '"' . $string . '"';
            }, \Yii::$app->myhelper->getStations()));
            $response= Loser::find()->where("station_id IN ($stations)")->orderBy("plays DESC")->limit($limit)->all();
        }
        else
        {
            $response= Loser::find()->orderBy("plays DESC")->limit($limit)->all();
        }
        if(isset($_POST['amount']) && $_POST['amount'] > 0 && $loadcheck){//Disburse amount
            TransactionHistories::processLosersDisbursements($response, $_POST['amount']);
            $this->redirect('loserpayout');
        }
        

        return $this->render('loserpayout', [
            'limit' =>  $limit,
            'loadcheck'=>$loadcheck,
            'response' => $response
        ]);
    }
    public function actionLogloser($limit)
    {
        Yii::$app->queue->priority(100)->push(new LogLoserJob(['limit'=>$limit]));
    }
    /**
        * Method to render growth trend graph
    */
    public function actionGrowthtrend(){
        $start_date= date('Y-m-d');
        $end_date = $start_date;
        if ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'daily' ) {
            $response= HourlyPerformanceReports::growthTrendData();
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'monthly' ) {
            $response= RevenueReport::monthlyGrowthTrendData();
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'range' ) {
                if ( isset( $_GET['from'] ) && isset( $_GET['to'] ) ) {
                        $end_date       = $_GET['to'];
                        $start_date     = $_GET['from'];
                        $date1    = strtotime( $end_date);
                        $date2    = strtotime( $start_date);
                        if ( $date1 < $date2 ) {
                                Yii::$app->session->setFlash('error', 'Error: start date should be before the end date' );
                        }
                        $response= RevenueReport::rangeGrowthTrendData($start_date,$end_date);
                } else {
                    $start_date= date('Y-m-01');
                    $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
                    $end_date = date("Y-m-$d");
                    $response= RevenueReport::rangeGrowthTrendData($start_date,$end_date);
                }
        } else {
            $response= HourlyPerformanceReports::growthTrendData();
        }
       
        return $this->render('growthtrend', [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'range' => json_encode($response['range'],JSON_NUMERIC_CHECK),
            'response' => json_encode($response['sum'],JSON_NUMERIC_CHECK)
        ]);
    }

        /**
        * Method to generate payout report
    */
    public function actionExportpayouts()
    {
        $file_name="financial_summary".date("Y-m-d-His").".pdf";
        $start_date= date('Y-m-d');
        $end_date = $start_date;
        if ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'daily' ) {
            $start_date= date('Y-m-d');
            $end_date = $start_date;
            $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
            $response2 = Commissions::getPresenterCommission($start_date,$end_date);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'monthly' ) {
            $start_date= date('Y-m-01');
            $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
            $end_date = date("Y-m-$d");
            $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
            $response2 = Commissions::getPresenterCommission($start_date,$end_date);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'range' ) {
                if ( isset( $_GET['from'] ) && isset( $_GET['to'] ) ) {
                        $end_date       = $_GET['to'];
                        $start_date     = $_GET['from'];
                        $date1    = strtotime( $end_date);
                        $date2    = strtotime( $start_date);
                        if ( $date1 < $date2 ) {
                                Yii::$app->session->setFlash('error', 'Error: start date should be before the end date' );
                        }
                        $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
                        $response2 = Commissions::getPresenterCommission($start_date,$end_date);
                } else {
                    $start_date= date('Y-m-01');
                    $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
                    $end_date = date("Y-m-$d");
                    $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
                    $response2 = Commissions::getPresenterCommission($start_date,$end_date);
                }
        } else {
            $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
            $response2 = Commissions::getPresenterCommission($start_date,$end_date);
        }
        $content = $this->renderFile('@app/views/report/partials/payouts_view.php', [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'response1' => $response1,
            'response2' => $response2
        ]);
        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_FILE,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '
                #bill-content-div{ font-family: Cambria,Georgia,serif !important; }
                table {
                    border-collapse: collapse;
                    width:96%;
                }
                table, th, td {
                    border: 1px solid black;
                }
                th{
                    text-align:center !important;
                    height:50px !important;
                }
                .bill_content{
                    font-family: Cambria, Georgia, serif; font-size: 14px; font-style: normal; font-variant: normal; font-weight: 400; line-height: 20px;
                    text-align:center !important;
                }
                .first_col{
                    padding-left:2px;
                }
            ',
            'defaultFont' => 'Cambria,Georgia,serif',
            'defaultFontSize' => '22',
             // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>['test'],
                'SetFooter'=>['{PAGENO}'],
            ]
        ]);
        // return the pdf output as per the destination setting
        $filepath = \Yii::$app->basePath . "/web/uploads/$file_name";
        $pdf->output($content, $filepath, Pdf::DEST_FILE);
        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".filesize($filepath));
        header("Content-Disposition: attachment; filename=$filepath");
        header("location: \"".basename($filepath)."\"");
        return Yii::$app->response->sendFile($filepath);
    }
    /**
        * Function to show Active Payouts report
        * @return type
    */
    public function actionPayouts(){ 
        $start_date= date('Y-m-d');
        $end_date = $start_date;
        if ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'daily' ) {
            $start_date= date('Y-m-d');
            $end_date = $start_date;
            $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
            $response2 = Commissions::getPresenterCommission($start_date,$end_date);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'monthly' ) {
            $start_date= date('Y-m-01');
            $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
            $end_date = date("Y-m-$d");
            $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
            $response2 = Commissions::getPresenterCommission($start_date,$end_date);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'range' ) {
                if ( isset( $_GET['from'] ) && isset( $_GET['to'] ) ) {
                        $end_date       = $_GET['to'];
                        $start_date     = $_GET['from'];
                        $date1    = strtotime( $end_date);
                        $date2    = strtotime( $start_date);
                        if ( $date1 < $date2 ) {
                                Yii::$app->session->setFlash('error', 'Error: start date should be before the end date' );
                        }
                        $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
                        $response2 = Commissions::getPresenterCommission($start_date,$end_date);
                } else {
                    $start_date= date('Y-m-01');
                    $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
                    $end_date = date("Y-m-$d");
                    $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
                    $response2 = Commissions::getPresenterCommission($start_date,$end_date);
                }
        } else {
            $response1 = Disbursements::getDisbursementByStation($start_date,$end_date);
            $response2 = Commissions::getPresenterCommission($start_date,$end_date);
        }
        return $this->render('payouts', [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'response1' => $response1,
        'response2' => $response2
        ]);
        
    }
    public function actionShowsummary()
    {
        $start_date= date('Y-m-d');
        $station = isset($_GET['station']) ? $_GET['station'] : null;
        $end_date = $start_date;
        if ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'daily' ) {
            $start_date= date('Y-m-d');
            $end_date = $start_date;
            $response=ShowSummary::getShowSummary($start_date,$end_date,$station);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'monthly' ) {
            $start_date= date('Y-m-01');
            $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
            $end_date = date("Y-m-$d");
            $response=ShowSummary::getShowSummary($start_date,$end_date,$station);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'range' ) {
                if ( isset( $_GET['from'] ) && isset( $_GET['to'] ) ) {
                        $end_date       = $_GET['to'];
                        $start_date     = $_GET['from'];
                        $date1    = strtotime( $end_date);
                        $date2    = strtotime( $start_date);
                        if ( $date1 < $date2 ) {
                                Yii::$app->session->setFlash('error', 'Error: start date should be before the end date' );
                        }
                        $response=ShowSummary::getShowSummary($start_date,$end_date,$station);
                } else {
                    $start_date= date('Y-m-01');
                    $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
                    $end_date = date("Y-m-$d");
                    $response=ShowSummary::getShowSummary($start_date,$end_date,$station);
                }
        } else {
            $response=ShowSummary::getShowSummary($start_date,$end_date,$station);
        }
        return $this->render('show_summary', [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'stations' => Stations::getStations(),
            'response' => $response
            ]);
    }
    
    public function actionExportshowsummary()
    {
        $filename="showSummary".date("Y-m-d-His").".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename);
        $output = fopen( 'php://output', 'w' );
        ob_start();
        fputcsv($output, ['STATION NAME','STATION SHOW NAME','TOTAL REVENUE','TOTAL COMMISSION','TOTAL PAYOUTS']);
        $start_date= date('Y-m-d');
        $end_date = $start_date;
        if ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'daily' ) {
            $start_date= date('Y-m-d');
            $end_date = $start_date;
            $response=ShowSummary::getShowSummary($start_date,$end_date);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'monthly' ) {
            $start_date= date('Y-m-01');
            $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
            $end_date = date("Y-m-$d");
            $response=ShowSummary::getShowSummary($start_date,$end_date);
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'range' ) {
                if (isset( $_GET['from'] ) && $_GET['from'] != '' && isset($_GET['to'])  && $_GET['to'] != '') {
                        $end_date       = $_GET['to'];
                        $start_date     = $_GET['from'];
                        $date1    = strtotime( $end_date);
                        $date2    = strtotime( $start_date);
                        if ( $date1 < $date2 ) {
                                Yii::$app->session->setFlash('error', 'Error: start date should be before the end date' );
                        }
                        $response=ShowSummary::getShowSummary($start_date,$end_date);
                } else {
                    $start_date= date('Y-m-01');
                    $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
                    $end_date = date("Y-m-$d");
                    $response=ShowSummary::getShowSummary($start_date,$end_date);
                }
        } else {
            $response=ShowSummary::getShowSummary($start_date,$end_date);
        }
        for($i=0;$i< count($response); $i++)
        {
            $row=$data[$i];
            fputcsv($output, [$response[$i]['station_name'],$response[$i]['station_show_name'],number_format($response[$i]['revenue']),number_format($response[$i]['commission']),number_format($response[$i]['payout'])]);
        }
        Yii::$app->end();
        return ob_get_clean();
        
    }

    public function actionLogdata($the_day)
    {
        for($i=0;$i<24;$i++)
        {
            if($the_day==date("Y-m-d") && $this->formatHour($i) >=date('H') )
            {
                break;
            }
            $hr=$this->formatHour($i);
            $from_time=$the_day." ".$hr;
            $count=HourlyPerformanceReports::checkDuplicate($the_day,$hr);
            if($count==0)
            {
                $stations=Stations::getActiveStations();
                for($j=0;$j<count($stations); $j++)
                {
                    try
                    {
                    $station=$stations[$j];
                    $model=new HourlyPerformanceReports();
                    $model->hour=$hr;
                    $model->hour_date=$the_day;
                    $model->unique_field=$the_day.$hr.$station->id;
                    $model->station_id=$station->id;
                    $model->amount=MpesaPayments::getStationTotalMpesa($from_time,$station->station_code)['amount'];
                    $mpesa_payments = MpesaPayments::getTotalMpesa($from_time)['total_mpesa'];
                    $transaction_histories = TransactionHistories::getTotalTransactions($from_time)['total_history'];
                    $model->invalid_codes=$mpesa_payments - $transaction_histories;
                    $model->total_amount=$mpesa_payments;
                    $model->day_total=MpesaPayments::getStationTotalMpesa($the_day,$station->station_code)['amount'];
                    $model->created_at=date("Y-m-d H:i:s");
                    $model->save(false);
                    }
                    catch (IntegrityException $e) {
                        //allow execution
                    }
                }
                
            }
        }
    }
    public function actionCommissionsummary()
    {
        
        if(isset($_GET['criterion']) && $_GET['criterion']=="monthly")
        {
            $start_date=date("Y-m-01");    
            $end_date=date("Y-m-".cal_days_in_month(CAL_GREGORIAN,date("m"),date("Y")));    
        }
        else if(isset($_GET['criterion']) && $_GET['criterion']=="daily")
        {
            $start_date=date("Y-m-d");    
            $end_date=date("Y-m-d");    
        }
        else if(isset($_GET['criterion']) && $_GET['criterion']=="range")
        {
            $start_date=(isset($_GET['from'])?$_GET['from']:date("Y-m-d"));
            $end_date=(isset($_GET['to'])?$_GET['to']:date("Y-m-d"));
        }
        else{
            $start_date=date("Y-m-d");    
            $end_date=date("Y-m-d");
        }
        $data=CommissionSummary::getCommissionReport($start_date,$end_date);
        $act = new \app\models\ActivityLog();
        $act -> desc = "commission_summary report";
        $act ->setLog();
        return $this->render('commission_summary', [
            'data' => $data
        ]);
    }
    public function actionDayupdate()
    {
        exit();//disabled to be run monthly
        $from_time=date("Y-m");
        MpesaPayments::calculateStationPercentage($from_time);
    }
    /**
     * Method to export commission
     */
    public function actionExportcommissionsummary()
    {
        $filename="commissionSummary".date("Y-M-d-His").".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        fputcsv($output, ['Station','Show','Timing','Target','Achieved','Net Revenue','Presenter Commission','Management Commission']);
        if(isset($_GET['criterion']) && $_GET['criterion']=="monthly")
        {
            $start_date=date("Y-m-01");    
            $end_date=date("Y-m-".cal_days_in_month(CAL_GREGORIAN,date("m"),date("Y")));    
        }
        else if(isset($_GET['criterion']) && $_GET['criterion']=="daily")
        {
            $start_date=date("Y-m-01");    
            $end_date=date("Y-m-".cal_days_in_month(CAL_GREGORIAN,date("m"),date("Y")));    
        }
        else{
            $start_date=(isset($_GET['from'])?$_GET['from']:date("Y-m-d"));
            //$end_date=(isset($_GET['to'])?$_GET['to']:date("Y-m-d",strtotime("+1 day",time())));
            $end_date=date("Y-m-".cal_days_in_month(CAL_GREGORIAN,date("m"),date("Y")));
        }
        $data=CommissionSummary::getCommissionReport($start_date,$end_date);
        for($i=0;$i< count($data); $i++)
        {
            $row=$data[$i];
            fputcsv($output, [$row['station_name'],$row['show_name'],$row['show_timing'],$row['target'],round($row['achieved']),round(($row['achieved']-$row['payout'])),round($row['presenter_commission']),round($row['station_commission'])]);
        }
        Yii::$app->end();
        return ob_get_clean();
    }
    public function actionDailyawarding()
    {
        if(isset($_GET['criterion']) && $_GET['criterion']=="monthly")
        {
            $start_date=date("Y-m-01");    
            $end_date=date("Y-m-".cal_days_in_month(CAL_GREGORIAN,date("m"),date("Y")));    
        }
        else{
            $start_date=(isset($_GET['from'])?$_GET['from']:date("Y-m-d"));
            $end_date=(isset($_GET['to'])?date('Y-m-d', strtotime($_GET['to']. ' + 1 day')):date("Y-m-d",strtotime("+1 day",time())));
        }
        $station = isset($_GET['station']) ? $_GET['station'] : null;
        $data=WinnerSummary::getAwardedSummary($start_date,$end_date,$station);
        $act = new \app\models\ActivityLog();
        $act -> desc = "daily_awarding report";
        $act ->setLog();
        return $this->render('daily_awarding', [
            'data' => $data
        ]);
    }
    /**
     * Method to export daily awarding
     */
    public function actionExportdailyawarding()
    {
        $filename="dailyAwarding".date("Y-m-d-His").".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        fputcsv($output, ['Station','Show','Prize','Timing','Awarded']);
        if(isset($_GET['criterion']) && $_GET['criterion']=="monthly")
        {
            $start_date=date("Y-m-01");    
            $end_date=date("Y-m-".cal_days_in_month(CAL_GREGORIAN,date("m"),date("Y")));    
        }
        else{
            $start_date=(isset($_GET['from']) && $_GET['from'] !=''?$_GET['from']:date("Y-m-d"));
            $end_date=(isset($_GET['to']) && $_GET['to'] !=''?date('Y-m-d', strtotime($_GET['to']. ' + 1 day')):date("Y-m-d",strtotime("+1 day",time())));
        }
        $data=WinnerSummary::getAwardedSummary($start_date,$end_date);
        $total=0;
        $count=count($data);
        for($i=0;$i<$count; $i++)
        {
            $row=$data[$i];
            $total+=$row['awarded'];
            fputcsv($output, [$row['station_name'],$row['show_name'],$row['prize_name'],$row['show_timing'],number_format($row['awarded'])]);
        }
        Yii::$app->end();
        return ob_get_clean();
        
    }
    public function actionRevenue()
    {
        if(isset($_GET['criterion']) && $_GET['criterion']=="monthly")
        {
            $start_date=date("Y-m-01");    
            $end_date=date("Y-m-".cal_days_in_month(CAL_GREGORIAN,date("m"),date("Y")));    
        }
        else{
            $start_date=(isset($_GET['from'])?$_GET['from']:date("Y-m-d"));
            $end_date=(isset($_GET['to'])?$_GET['to']:date("Y-m-d"));
        }
        $station = isset($_GET['station']) ? $_GET['station'] : null;
        $resp=RevenueReport::getRevenueReport($start_date,$end_date,$station);
        $act = new \app\models\ActivityLog();
        $act -> desc = "revenue report";
        $act ->setLog();
        return $this->render('revenue', [
            'data' => $resp
        ]);
    }
    public function actionExportrevenue()
    {
        $filename="revenue".date("Y-m-d-His").".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        fputcsv($output, ['Day','Total Revenue','Total Awarded','Net Revenue']);
        if(isset($_GET['criterion']) && $_GET['criterion']=="monthly")
        {
            $start_date=date("Y-m-01");    
            $end_date=date("Y-m-".cal_days_in_month(CAL_GREGORIAN,date("m"),date("Y")));    
        }
        else{
            $start_date=(isset($_GET['from'])?$_GET['from']:date("Y-m-d"));
            $end_date=(isset($_GET['to'])?date('Y-m-d', strtotime($_GET['to']. ' + 1 day')):date("Y-m-d",strtotime("+1 day",time())));
        }
        $resp=RevenueReport::getRevenueReport($start_date,$end_date);
        $total_revenue=0;
        $total_awarded=0;
        $total_net_revenue=0;
        $count=count($resp);
        for($i=0;$i<$count; $i++)
        {
            $row=$resp[$i];
            $total_revenue+=$row->total_revenue;
            $total_awarded+=$row->total_awarded;
            $total_net_revenue+=$row->net_revenue;
            fputcsv($output, [$row->revenue_date,number_format($row->total_revenue),number_format($row->total_awarded),number_format($row->net_revenue)]);
        }
        Yii::$app->end();
        return ob_get_clean();
        
    }
    public function actionPresentercommission()
    {
        $data=Commissions::presenterCommission(Yii::$app->user->identity->id);
        
        return $this->render('presenter_commission', [
            'data' => $data
        ]);
    }
    public function actionDashboard()
    {}
    private function formatHour($hr)
    {
        if($hr < 10)
        {
            return '0'.$hr;
        }
        else
        {
            return $hr;
        }
    }
    public function actionDemo()
    {
        $stations=Stations::getActiveStations();
        $today=date("Y-m-d");
        $current_time = date("H");
        $transaction_history_result = array();
        $start=0;
        $end=0;
        if($current_time >= 0 && $current_time < 8){
            $start=0;
            $end=8;
        }
        else if($current_time >= 8 && $current_time < 16){
            $start=8;
            $end=16;
        }
        else if($current_time >= 16 && $current_time < 24){
            $start=16;
            $end=24;
        }
        for($i = $start; $i< $end; $i++){
            $i=$this->formatHour($i);
            $total_amount = 0;
            $total_invalid_codes = 0;
            $from_time=$today." ".$i;
            $mpesa_payments = MpesaPayments::getTotalMpesa($from_time);
            $mpesa_payments=$mpesa_payments['total_mpesa'];
            $transaction_histories = TransactionHistories::getTotalTransactions($from_time);
            $transaction_histories=$transaction_histories['total_history'];
            $invalid_codes = $mpesa_payments - $transaction_histories;
            $total_invalid_codes = $total_invalid_codes + $invalid_codes;
            $total_amount = $total_amount + $mpesa_payments;
            $station_result = Stations::getStationResult($from_time);
            array_push($transaction_history_result,
                array(
                    'hour' => $i,
                    'hour_results' => $station_result,
                    'total_amount' => $total_amount,
                    'total_invalid_codes' => $total_invalid_codes,
                ));
        }
        $overall_station_total_result = array();
        $start_period=$today." ".$start.":00";
        $end_period=$today." ".($end-1).":59";
        $station_total_result = Stations::getStationTotalResult($start_period,$end_period);
        $overall_station_total_result = Stations::getDayStationTotalResult($today);
        $mpesa_payments = MpesaPayments::getTotalMpesa($today);
        $mpesa_payments = $mpesa_payments['total_mpesa'];
        $transaction_histories = TransactionHistories::getTotalTransactions($today);
        $transaction_histories = $transaction_histories['total_history'];
        $invalid_codes = $mpesa_payments - $transaction_histories;
        $json_array = array(
            'transaction_histories' => $transaction_history_result,
            'overall_station_totals' => $overall_station_total_result,
            'overall_invalid_codes' => $invalid_codes,
            'overall_total_amount' => $mpesa_payments,
            'station_totals' => $station_total_result,
        );

        $hourly = $json_array;
        return $hourly;
    }

    public function actionShowlog($start_date,$end_date)
    {
        while($start_date <= $end_date)
        {
            ShowSummary::logShowSummary($start_date);
            $start_date=date('Y-m-d', strtotime($start_date . ' +1 day'));
        }
    }
    public function actionLogsitereport()
    {
        Yii::$app->analyticsqueue->push(new SiteReportJob());
    }
    public function actionLasthour()
    {
        if(date("H")=="00")
        {
            $the_day=date('Y-m-d',strtotime("yesterday"));
            $hr="23";
        }
        else
        {
            $the_day=date("Y-m-d");
            $hr=Myhelper::formatHour(date('H')-1);
        }
        Yii::$app->analyticsqueue->push(new LastHourJob(['the_day'=>$the_day,'hr'=>$hr]));
    }
    public function actionSetreport()
    {
        SiteReport::setSiteReport();
    }
    public function actionCleanhourly($log_date)
    {
        for($j=0;$j<24;$j++)
            {
                $hr=Myhelper::formatHour($j);
                HourlyPerformanceReports::LastHour($log_date,$hr);
            }
    }
    public function actionHourclean($log_date,$hr)
    {
        HourlyPerformanceReports::LastHour($log_date,$hr);
    }
    public function actionLogshowsummary()
    {
        Yii::$app->analyticsqueue->push(new ShowSummaryJob());
    }
    public function actionUpdateshow($start_date)
    {
        ShowSummary::updateShowSummary($start_date);
    }
    public function actionLogcommission()
    {        
        Yii::$app->analyticsqueue->push(new LogCommissionJob());
    }
    public function actionShow($m,$y)
    {
        $days=cal_days_in_month(CAL_GREGORIAN,date($m),date($y));
        for($i=1;$i<=$days; $i++)
        {
            $day=Myhelper::formatHour($i);
            $month=Myhelper::formatHour($m);
            $date=date("$y-$month-$day");
            ShowSummary::logShowSummary($date);
        }
    }
    public function actionLogawards()
    {        
        Yii::$app->analyticsqueue->push(new AwardsJob());
    }
    public function actionLogrevenue($date=NULL)
    {
        if($date!=NULL)
        {
            $revenue_date=$date;
        }
        else if(date("H")=="00")
        {
            $revenue_date= date('Y-m-d',strtotime('yesterday'));
        }
        else
        {
            $revenue_date= date('Y-m-d');
        }
        Yii::$app->analyticsqueue->push(new RevenueJob(['revenue_date'=>$revenue_date]));
    }
    public function actionLogger($m,$y)
    {
        
        $days=cal_days_in_month(CAL_GREGORIAN,date($m),date($y));
        for($i=1;$i<=$days; $i++)
        {
            $day=Myhelper::formatHour($i);
            $month=Myhelper::formatHour($m);
            $date=date("$y-$month-$day");
            MpesaPayments::logRevenue($date);
        }
    }
    public function runLogs($log_date)
    {
        //Commissions::logCommission($log_date);
        //WinningHistories::logDailyAwards($log_date);
        MpesaPayments::logRevenue($log_date);
    }
    public function actionLogy($date=NULL)
    {
        if($date==NULL)
        {
            $date=date("Y-m-d",strtotime("- 1 day"));
        }
        MpesaPayments::logRevenue($date);
    }
    public function actionFullmonth($month,$start,$end)
    {
        for($i=$start;$i<=$end; $i++)
        {
            $day=Myhelper::formatHour($i);
            $month=Myhelper::formatHour($month);
            $revenue_date=date("Y-$month-$day");
            MpesaPayments::logRevenue($revenue_date);
        }
    }

    //removed action
    public function actionPlayercurrent($start_date,$end_date,$station_id=null)
    {
        $this->playerDataCurrent($start_date,$end_date,$station_id);

    }
    public function actionPlayerarchive($start_date,$end_date,$station_id=null)
    {
        $this->playerDataarchive($start_date,$end_date,$station_id);

    }
    public function actionPlayerlastmonth()
    {
        $this->lastMonth();

    }
    public function actionStation($station)
    {
        $this->stationData($station);

    }
    private function stationData($station)
    {
        $response=[];
        $response=TransactionHistories::find()->select('reference_phone')->where(["reference_code"=>$station])->distinct()->all();
        $filename=$station."_".date("Y-m-d-His").".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        
        for($i=0;$i<count($response); $i++)
        {
            $arr=[];
              $row=$response[$i];
              array_push($arr,$row['reference_phone']);
              fputcsv( $output,$arr);
            
        }
        Yii::$app->end();
        return ob_get_clean();
    }
    private function playerDataCurrent($start_date,$end_date,$station_id)
    {
       /* $archive=[];
        $current=[];
        $archive=ArchivedTransactionHistories::getUniquePlayers();
        $current=TransactionHistories::getUniquePlayers();
        TransactionHistories::merge($archive,$current);*/
        $current=TransactionHistories::getUniquePlayers($start_date,$end_date,$station_id);
        $filename=SENDER_NAME."current".".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        $data=['CUSTOMER NAME','PHONE NUMBER','STATION'];
        fputcsv( $output,$data);
        foreach($current as $row)
        {
            $arr=[];
            array_push($arr,$row['reference_name']);
            array_push($arr,$row['reference_phone']);
            array_push($arr,$row['name']);
            fputcsv( $output,$arr);
        }
        Yii::$app->end();
        return ob_get_clean();
    }
    private function playerDataArchive($start_date,$end_date,$station_id)
    {
        $archive=ArchivedTransactionHistories::getUniquePlayers($start_date,$end_date,$station_id);
        $filename=SENDER_NAME."archive".".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        $data=['CUSTOMER NAME','PHONE NUMBER','STATION'];
        fputcsv( $output,$data);
        foreach($archive as $row)
        {
            $arr=[];
            array_push($arr,$row['reference_name']);
            array_push($arr,$row['reference_phone']);
            array_push($arr,$row['name']);
            fputcsv( $output,$arr);
        }
        Yii::$app->end();
        return ob_get_clean();
    } 
    private function lastMonth()
    {
        $current=TransactionHistories::getUniquePlayersInRange();
        $filename=SENDER_NAME."lastmonth".".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        $data=['CUSTOMER NAME','PHONE NUMBER','STATION'];
        fputcsv( $output,$data);
        foreach($current as $row)
        {
            $arr=[];
            array_push($arr,$row['reference_name']);
            array_push($arr,$row['reference_phone']);
            array_push($arr,$row['name']);
            fputcsv( $output,$arr);
        }
        Yii::$app->end();
        return ob_get_clean();
    }   
    public function actionBacklog($month,$start,$end)
    {
        while($start <= $end)
        {
            $start=(strlen($start)==1)?"0$start":$start;
            $month=(strlen($month)==1)?"0$month":$month;
            $revenue_date=date("Y")."-$month-$start";
            MpesaPayments::logRevenue($revenue_date);
           //$url=Url::base('https')."/report/logrevenue?date=2022-$month-$start";
           //Myhelper::curlGet($url);
           $start++;
            
        }

    }
    public function actionMerge($file1,$file2,$file3)
    {
        ini_set('memory_limit', '1024M');
        $file1="/mnt/c/Users/Cesay/Downloads/dbs/".$file1.".csv";
        $file2="/mnt/c/Users/Cesay/Downloads/dbs/".$file2.".csv";
        $handle = fopen($file1, "r");
        $seen=[];
        $final=[];
        $filename=$file3.date("Ymd").".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        //$data=['CUSTOMER NAME','PHONE NUMBER','STATION'];
        //fputcsv( $output,$data);
        while (($row = fgetcsv($handle, 1000, ",")) !== false) 
        {
            $unique_field=trim($row[0]).trim($row[1]).trim($row[2]);
            $new=[];
            if(!in_array($unique_field,$seen))
            {
                $new=[$row[0],$row[1],$row[2],$unique_field];
                array_push($final,$new);
                $arr=[];
                array_push($arr,$row[0]);
                array_push($arr,$row[1]);
                array_push($arr,$row[2]);
                fputcsv( $output,$arr);
            }

        }
        $handle1 = fopen($file2, "r");
        while (($row = fgetcsv($handle1, 1000, ",")) !== false) 
        {
            $unique_field=trim($row[0]).trim($row[1]).trim($row[2]);
            $new=[];
            if(!in_array($unique_field,$seen))
            {
                $new=[$row[0],$row[1],$row[2],$unique_field];
                array_push($final,$new);
                $arr=[];
                array_push($arr,$row[0]);
                array_push($arr,$row[1]);
                array_push($arr,$row[2]);
                fputcsv( $output,$arr);
            }

        }
        Yii::$app->end();
        return ob_get_clean();
    }

    public function actionTotaluniqueplayers($file)
    {
        ini_set('memory_limit', '1024M');
        // Read the CSV file into an array
        $rows = array_map('str_getcsv', file($file));
        $phoneNumbers = array_column($rows, 1); 

        if (!empty($phoneNumbers) && $phoneNumbers[0] == 'PHONE NUMBER') {
            array_shift($phoneNumbers);
        }
        $phoneNumbers = array_map('trim', $phoneNumbers);

        $uniquePhoneNumbers = array_unique($phoneNumbers);
        $filename="UNIQUE_PLAYERS.csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        $data=['PHONE NUMBER'];
        fputcsv( $output,$data);
        foreach($uniquePhoneNumbers as $row)
        {
            $arr=[];
            array_push($arr,$row);
            fputcsv( $output,$arr);
        }
        Yii::$app->end();
        return ob_get_clean();

    }
    public function actionStake()
    {
        try{
            $filename=MAIN_DB."-stakes-".date("Y-m-d-His").".csv";
            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename='.$filename );
            $sql="select a.id,a.reference_phone,a.reference_code,b.name as station_name,c.name as station_show,a.amount,a.created_at from transaction_histories a left join stations b on a.station_id=b.id left join station_shows c 
            on a.station_show_id=c.id";
            $data=Myhelper::getAll($sql);
            $analytics_sql = "select a.id,a.reference_phone,a.reference_code,b.name as station_name,c.name as station_show,a.amount,a.created_at from transaction_histories a left join stations b on a.station_id=b.id left join station_shows c 
            on a.station_show_id=c.id";
            $analytics_data = Yii::$app->analytics_db->createCommand($analytics_sql)->queryAll();
            $mergedData = array_merge($data, $analytics_data);
            // $unique_merged_data = array_map("unserialize", array_unique(array_map("serialize", $mergedData)));
            $titles=['id','reference_phone','reference_code','station_name','station_show','amount','created_at'];
            $output = fopen( 'php://output', 'w' );
            ob_start();
            fputcsv( $output,$titles);
            foreach ($mergedData as $row) {
                fputcsv($output, $row);
            }
            Yii::$app->end();
            ob_get_clean();

        }catch(Throwable $th){
            var_dump($th);
        }

    }  
    public function actionShows()
    {
        $filename=MAIN_DB."-shows-".date("Y-m-d-His").".csv";
        $sql='select a.name as station_name,b.name as show_name,b.start_time,b.end_time,b.monday,b.tuesday,b.wednesday,b.thursday,
        b.friday,b.saturday,b.sunday,(select GROUP_CONCAT(CONCAT(j.first_name," ",j.last_name)) from users j left join station_show_presenters k
        on j.id=k.presenter_id where station_show_id=b.id) as presenters from stations a left join station_shows b on a.id=b.station_id';
        $data=Myhelper::getAll($sql);
        $titles=['station_name','show_name','start_time','end_time','monday','tuesday','wednesday','thursday','friday','saturday','sunday','presenters'];
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        fputcsv( $output,$titles);
        foreach($data as $row)
        {
            fputcsv( $output,$row);
        }
        Yii::$app->end();
        ob_get_clean();
    }
    public function actionWinner()
    {
        $filename=MAIN_DB."-winners-".date("Y-m-d-His").".csv";
        $sql='select b.name as station_name,c.name as show_name,a.reference_phone,a.reference_code,a.amount,a.created_at,CONCAT(d.first_name," ",d.last_name) as presenter_name from winning_histories a 
        left join stations b on a.station_id=b.id left join station_shows c on a.station_show_id=c.id 
        left join users d on a.presenter_id=d.id';
        $data=Myhelper::getAll($sql);
        $titles=['station_name','show_name','reference_phone','reference_code','amount','created_at','presenter_name'];
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename='.$filename );
        $output = fopen( 'php://output', 'w' );
        ob_start();
        fputcsv( $output,$titles);
        foreach($data as $row)
        {
            fputcsv( $output,$row);
        }
        Yii::$app->end();
        ob_get_clean();
    } 

}
?>
