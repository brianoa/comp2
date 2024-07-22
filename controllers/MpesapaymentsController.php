<?php

namespace app\controllers;

use app\components\ArchiveMoneyJob;
use Yii;
use app\models\MpesaPayments;
use app\models\Outbox;
use app\models\MpesaPaymentsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Webpatser\Uuid\Uuid;
use app\components\Myhelper;
use yii\web\UploadedFile;
use yii\db\IntegrityException;
use app\components\DepositJob;
use app\components\SetStationJob;
use app\models\ArchivedMpesaPayments;
use app\models\Customer;
use app\models\Stations;
use app\models\TransactionHistories;
use Exception;

/**
 * MpesapaymentsController implements the CRUD actions for MpesaPayments model.
 */
class MpesapaymentsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update', 'index', 'airtel', 'vodacom', 'correctairteldate', 'tigo', 'halotel', 'download'],
                'rules' => [
                    [
                        'actions' => ['create', 'update', 'index', 'airtel', 'vodacom', 'correctairteldate', 'tigo', 'halotel', 'download'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(22));
                                return in_array(Yii::$app->user->identity->email, $users);
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

    /**
     * Lists all MpesaPayments models.
     * @return mixed
     */
    public function actionIndex()
    {
        try {
            $searchModel  = new MpesaPaymentsSearch();
            $model        = new MpesaPayments();
            $dataProvider = Yii::$app->myhelper->getdataprovider($searchModel);
            if ($model->load(Yii::$app->request->post()) && isset($_POST['MpesaPayments']['excelfile'])) {
                $model->excelfile = UploadedFile::getInstance($model, 'excelfile');

                if ($model->upload()) {
                    #$model->logFileUpload();
                    Yii::$app->session->setFlash('success', 'file specimenuploaded');
                }
            }
        } catch (\Exception $exc) {
            Yii::$app->session->setFlash('error', $exc->getMessage());
            Yii::error($exc->getMessage());
            Yii::error($exc->getTraceAsString());
        }
        $act = new \app\models\ActivityLog();
        $act->desc = "mpesapayment report";
        $act->setLog();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model
        ]);
    }

    /**
     * Displays a single MpesaPayments model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MpesaPayments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MpesaPayments();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    //function for savebatch transactions
    public function actionSave()
    {
        Myhelper::checkRemoteAddress();
        $jsondata = file_get_contents('php://input');
        $value = json_decode($jsondata, true);
        $model = new MpesaPayments();
        $model->id = Uuid::generate()->string;
        $model->TransID = $value['TransID'];
        $model->FirstName = $value['FirstName'];
        $model->MiddleName = $value['MiddleName'];
        $model->LastName = $value['LastName'];
        $model->MSISDN = $value['MSISDN'];
        $model->InvoiceNumber = $value['InvoiceNumber'];
        $model->BusinessShortCode = $value['BusinessShortCode'];
        $model->ThirdPartyTransID = $value['ThirdPartyTransID'];
        $model->TransactionType = $value['TransactionType'];
        $model->OrgAccountBalance = $value['OrgAccountBalance'];
        $model->BillRefNumber = $value['BillRefNumber'];
        $model->TransAmount = $value['TransAmount'];
        $model->created_at = date("Y-m-d H:i:s");
        $model->updated_at = date("Y-m-d H:i:s");
        $model->save(false);
        $response['status'] = 'success';
        $response['message'] = 'success';
        $response['data'] = [];
        \Yii::$app->response->data = json_encode($response);
    }
    public function actionInsertpayment($reference_code, $amount, $limit)
    {
        if (gethostname() != 'kuta') {
            exit();
        }
        for ($i = 0; $i < $limit; $i++) {
            try {
                $name_suffix = rand();
                $model = new MpesaPayments();
                $model->id = Uuid::generate()->string;
                $model->TransID = date('YmdHisu') . $i;
                $model->FirstName = "first" . $name_suffix;
                $model->MiddleName = "mid" . $name_suffix;
                $model->LastName = "last" . $name_suffix;
                $model->MSISDN = "2547" . rand(10000000, 99999999);
                $model->InvoiceNumber = "demo";
                $model->BusinessShortCode = "demo";
                $model->ThirdPartyTransID = "demo";
                $model->TransactionType = "demo";
                $model->OrgAccountBalance = 0;
                $model->BillRefNumber = $reference_code;
                $model->TransAmount = $amount;
                $model->created_at = date("Y-m-d H:i:s");
                $model->updated_at = date("Y-m-d H:i:s");
                $model->save(false);
                //Yii::$app->queue->priority(10)->push(new DepositJob(['id'=>$model->id]));
            } catch (IntegrityException $e) {
                //allow execution
            }
        }
    }

    /**
     * Updates an existing MpesaPayments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing MpesaPayments model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the MpesaPayments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return MpesaPayments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MpesaPayments::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public static function actionRemovedups()
    {
        Myhelper::checkRemoteAddress();
        $dups = MpesaPayments::getDuplicates();
        for ($i = 0; $i < count($dups); $i++) {
            $row = $dups[$i];
            MpesaPayments::removeDups($row['TransID'], $row['total'] - 1);
        }
    }
    public function actionPay()
    {

        if (Myhelper::checkLocalToken()) {
            $jsondata = file_get_contents('php://input');
            $data = json_decode($jsondata);
            Yii::$app->queue->priority(10)->push(new DepositJob(['id' => $data->id]));
        }
    }
    public function actionAirtel()
    {
        // ini_set( 'memory_limit', '512M' );
        // ini_set( 'max_execution_time', '3000' );
        $success = [];
        $error   = [];
        if (isset($_POST['submit'])) {
            $file = $_FILES['file']['tmp_name'];
            //			$file    = "confirmed_payment.csv";
            $success = [];
            $error   = [];
            $row     = 1;
            if (($handle = fopen($file, "r")) !== false) {
                while (($data = fgetcsv($handle, 2000, ",")) !== false) {
                    $transaction_number = trim(isset($data[1]) ? $data[1] : NULL);
                    //$reference=trim(isset($data[9]) && ctype_alnum($data[9])?$data[9]:NULL);
                    $reference = (isset($data[9]) && ctype_alnum($data[9])) ? trim($data[9]) : NULL;
                    $trans_type = trim(isset($data[8]) ? $data[8] : NULL);
                    $date = trim(isset($data[4]) ? $data[4] : NULL);
                    $date = date("Y-m-d", strtotime($date));
                    $phone = trim(isset($data[2]) ? $data[2] : NULL);
                    $phone = "255" . $phone;
                    $amount = trim(isset($data[3]) ? $data[3] : NULL);
                    $balance = trim(isset($data[11]) ? $data[11] : NULL);
                    if (
                        !empty($transaction_number) && !empty($reference)
                        && !empty($trans_type) && $trans_type == "Transaction Success"
                        && !empty($date) && !empty($phone) && !empty($amount) && is_numeric($amount)
                        && (($amount == 1000 && !Myhelper::compareCode($reference, 'YANGA/111')) || ($amount == 2000 && Myhelper::compareCode($reference, 'YANGA/111')))
                        && !empty($balance)
                    ) {
                        $check_if_exists = MpesaPayments::find()->where(['TransID' => $transaction_number])->one();
                        if ($check_if_exists == NULL) {
                            $mod = new MpesaPayments();
                            $mod->id = Uuid::generate()->string;
                            $mod->TransID = $transaction_number;
                            $mod->TransAmount = $amount;
                            $mod->FirstName = NULL;
                            $mod->MiddleName = NULL;
                            $mod->LastName = NULL;
                            $mod->MSISDN = $phone;
                            $mod->BillRefNumber = $reference;
                            $mod->OrgAccountBalance = $balance;
                            //$mod -> TransactionType =; 
                            $mod->created_at = $date;
                            $mod->updated_at = date('Y-m-d H:i:s');
                            $mod->save(FALSE);
                            Yii::$app->queue->push(new DepositJob(['id' => $mod->id]));
                            array_push($success, $row);
                        }
                        array_push($error, $row);
                    }
                    $row++;
                }
                fclose($handle);
            }
        }

        return $this->render(
            'airtel',
            [
                'success' => $success,
                'error'   => $error
            ]
        );
    }
    public function actionVodacom()
    {
        // ini_set( 'memory_limit', '512M' );
        ini_set('max_execution_time', '3000');
        $success = [];
        $error   = [];
        if (isset($_POST['submit'])) {
            $file = $_FILES['file']['tmp_name'];
            $reference = $_POST['reference'];
            //			$file    = "confirmed_payment.csv";
            $success = [];
            $error   = [];
            $row     = 1;
            if (($handle = fopen($file, "r")) !== false) {
                while (($data = fgetcsv($handle, 2000, ",")) !== false) {

                    $transaction_number = trim(isset($data[0]) ? $data[0] : NULL);
                    //$reference=trim(isset($data[9]) && ctype_alnum($data[9])?$data[9]:NULL);
                    //$reference=(isset($data[3]) && ctype_alnum($data[3]))?trim(str_replace( ' ', '', explode( "Acc.", $data[3] )[1] )):NULL;
                    $date = trim(isset($data[1]) ? $data[1] : NULL);
                    $date = date("Y-m-d H:i:s", strtotime($date));
                    $phone = trim(isset($data[10]) ? explode("-", $data[10])[0] : NULL);
                    $amount = trim(isset($data[6]) ? $data[6] : NULL);
                    $balance = 0;
                    if (
                        !empty($transaction_number) && !empty($reference)
                        && !empty($date) && !empty($phone) && !empty($amount) && is_numeric($amount) && $amount == 1000
                    ) {
                        $check_if_exists = MpesaPayments::find()->where(['TransID' => $transaction_number])->one();
                        if ($check_if_exists == NULL) {
                            $mod = new MpesaPayments();
                            $mod->id = Uuid::generate()->string;
                            $mod->TransID = $transaction_number;
                            $mod->TransAmount = $amount;
                            $mod->FirstName = NULL;
                            $mod->MiddleName = NULL;
                            $mod->LastName = "vodacom";
                            $mod->MSISDN = $phone;
                            $mod->BillRefNumber = $reference;
                            $mod->OrgAccountBalance = $balance;
                            $mod->created_at = $date;
                            $mod->updated_at = date('Y-m-d H:i:s');
                            $mod->save(FALSE);
                            Yii::$app->queue->push(new DepositJob(['id' => $mod->id]));
                            array_push($success, $row);
                        }
                        array_push($error, $row);
                    }
                    $row++;
                }
                fclose($handle);
            }
        }

        return $this->render(
            'vodacom',
            [
                'success' => $success,
                'error'   => $error
            ]
        );
    }
    public function actionVoda()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '3000');
        $success = [];
        $error   = [];
        if (isset($_POST['submit'])) {
            $file = $_FILES['file']['tmp_name'];
            $reference = $_POST['reference'];
            //			$file    = "confirmed_payment.csv";
            $success = [];
            $error   = [];
            $row     = 1;
            if (($handle = fopen($file, "r")) !== false) {
                while (($data = fgetcsv($handle, 2000, ",")) !== false) {

                    $transaction_number = trim(isset($data[2]) ? $data[2] : NULL);
                    $date = trim(isset($data[0]) ? $data[0] : NULL);
                    $date = date("Y-m-d H:i:s", strtotime($date));
                    $phone = trim(isset($data[3]) ? explode("-", $data[3])[0] : NULL);
                    $amount = trim(isset($data[4]) ? $data[4] : NULL);
                    $balance = 0;
                    if (
                        !empty($transaction_number) && !empty($reference)
                        && !empty($date) && !empty($phone) && !empty($amount) && is_numeric($amount) && $amount == 1000
                    ) {
                        $check_if_exists = MpesaPayments::find()->where(['TransID' => $transaction_number])->one();
                        if ($check_if_exists == NULL) {
                            $phone = explode(" ", $phone)[3];
                            $reference_data = explode(".", $data[3])[1];
                            $mod = new MpesaPayments();
                            $mod->id = Uuid::generate()->string;
                            $mod->TransID = $transaction_number;
                            $mod->TransAmount = $amount;
                            $mod->FirstName = NULL;
                            $mod->MiddleName = NULL;
                            $mod->LastName = "vodacom";
                            $mod->MSISDN = $phone;
                            $mod->BillRefNumber = $reference_data;
                            $mod->OrgAccountBalance = $balance;
                            $mod->created_at = $date;
                            $mod->updated_at = date('Y-m-d H:i:s');
                            $mod->save(FALSE);
                            Yii::$app->queue->push(new DepositJob(['id' => $mod->id]));
                            array_push($success, $row);
                        }
                        array_push($error, $row);
                    }
                    $row++;
                }
                fclose($handle);
            }
        }

        return $this->render(
            'voda',
            [
                'success' => $success,
                'error'   => $error
            ]
        );
    }
    public function actionTigo()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '3000');
        $success = [];
        $error   = [];
        if (isset($_POST['submit'])) {
            $file = $_FILES['file']['tmp_name'];
            $reference = $_POST['reference'];
            //			$file    = "confirmed_payment.csv";
            $success = [];
            $error   = [];
            $row     = 1;
            if (($handle = fopen($file, "r")) !== false) {
                while (($data = fgetcsv($handle, 2000, ",")) !== false) {

                    $transaction_number = trim(isset($data[0]) ? $data[0] : NULL);
                    $phone = trim(isset($data[1]) ? $data[1] : NULL);
                    $reference = "PESA";
                    $date = trim(isset($data[5]) ? $data[5] : NULL);
                    $time = trim(isset($data[6]) ? $data[6] : NULL);
                    $amount = trim(isset($data[4]) ? $data[4] : NULL);
                    $balance = 0;
                    if (
                        !empty($transaction_number) && !empty($reference)
                        && !empty($date) && !empty($phone) && !empty($amount) && is_numeric($amount) && $amount == 1000
                    ) {
                        $check_if_exists = MpesaPayments::find()->where(['TransID' => $transaction_number])->one();
                        if ($check_if_exists == NULL) {
                            $mod = new MpesaPayments();
                            $mod->id = Uuid::generate()->string;
                            $mod->TransID = $transaction_number;
                            $mod->TransAmount = $amount;
                            $mod->FirstName = NULL;
                            $mod->MiddleName = NULL;
                            $mod->LastName = NULL;
                            $mod->MSISDN = $phone;
                            $mod->BillRefNumber = $reference;
                            $mod->OrgAccountBalance = $balance;
                            $mod->created_at = date("Y-m-d", strtotime($date)) . " " . date("H:i:s", strtotime($time));
                            $mod->updated_at = date('Y-m-d H:i:s');
                            $mod->save(FALSE);
                            Yii::$app->queue->push(new DepositJob(['id' => $mod->id]));
                            array_push($success, $row);
                        }
                        array_push($error, $row);
                    }
                    $row++;
                }
                fclose($handle);
            }
        }

        return $this->render(
            'tigo',
            [
                'success' => $success,
                'error'   => $error
            ]
        );
    }
    public function actionTigonew()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '3000');
        $success = [];
        $error   = [];
        if (isset($_POST['submit'])) {
            $file = $_FILES['file']['tmp_name'];
            $reference = $_POST['reference'];
            //			$file    = "confirmed_payment.csv";
            $success = [];
            $error   = [];
            $row     = 1;
            if (($handle = fopen($file, "r")) !== false) {
                while (($data = fgetcsv($handle, 2000, ",")) !== false) {

                    $transaction_number = trim(isset($data[4]) ? $data[4] : NULL);
                    $phone = trim(isset($data[0]) ? $data[0] : NULL);
                    $reference = "PESA";
                    $date = trim(isset($data[5]) ? $data[9] : NULL);
                    $time = trim(isset($data[6]) ? $data[10] : NULL);
                    $amount = trim(isset($data[7]) ? $data[7] : NULL);
                    $balance = 0;
                    if (
                        !empty($transaction_number) && !empty($reference)
                        && !empty($date) && !empty($phone) && !empty($amount) && is_numeric($amount) && $amount == 1000
                    ) {
                        $check_if_exists = MpesaPayments::find()->where(['TransID' => $transaction_number])->one();
                        if ($check_if_exists == NULL) {
                            $mod = new MpesaPayments();
                            $mod->id = Uuid::generate()->string;
                            $mod->TransID = $transaction_number;
                            $mod->TransAmount = $amount;
                            $mod->FirstName = NULL;
                            $mod->MiddleName = NULL;
                            $mod->LastName = NULL;
                            $mod->MSISDN = $phone;
                            $mod->BillRefNumber = $reference;
                            $mod->OrgAccountBalance = $balance;
                            $mod->created_at = date("Y-m-d", strtotime($date)) . " " . date("H:i:s", strtotime($time));
                            $mod->updated_at = date('Y-m-d H:i:s');
                            $mod->save(FALSE);
                            Yii::$app->queue->push(new DepositJob(['id' => $mod->id]));
                            array_push($success, $row);
                        }
                        array_push($error, $row);
                    }
                    $row++;
                }
                fclose($handle);
            }
        }

        return $this->render(
            'tigonew',
            [
                'success' => $success,
                'error'   => $error
            ]
        );
    }
    public function actionHalotel()
    {
        $success = [];
        $error   = [];
        if (isset($_POST['submit'])) {
            $file = $_FILES['file']['tmp_name'];
            $platform = PAYBILL;
            //			$file    = "confirmed_payment.csv";
            $success = [];
            $error   = [];
            $row     = 1;
            if (($handle = fopen($file, "r")) !== false) {
                while (($data = fgetcsv($handle, 2000, ",")) !== false) {

                    $transaction_number = trim(isset($data[0]) ? $data[0] : NULL);
                    $phone = trim(isset($data[6]) ? $data[6] : NULL);
                    $business_code = trim(isset($data[4]) ? $data[4] : NULL);
                    $reference = trim(isset($data[8]) ? $data[8] : NULL);
                    $datetime = trim(isset($data[1]) ? $data[1] : NULL);
                    $amount = trim(isset($data[7]) ? $data[7] : NULL);
                    $balance = 0;
                    if (
                        !empty($transaction_number) && !empty($reference)
                        && !empty($datetime) && !empty($phone) && !empty($amount) && is_numeric($amount) && $amount == 1000 && $business_code == $platform
                    ) {
                        $check_if_exists = MpesaPayments::find()->where(['TransID' => $transaction_number])->one();
                        if ($check_if_exists == NULL) {
                            $mod = new MpesaPayments();
                            $mod->id = Uuid::generate()->string;
                            $mod->TransID = $transaction_number;
                            $mod->TransAmount = $amount;
                            $mod->FirstName = NULL;
                            $mod->MiddleName = NULL;
                            $mod->LastName = "Halotel";
                            $mod->MSISDN = $phone;
                            $mod->BillRefNumber = $reference;
                            $mod->OrgAccountBalance = $balance;
                            $mod->created_at = date("Y-m-d H:i:s", strtotime($datetime));
                            $mod->updated_at = date('Y-m-d H:i:s');
                            $mod->save(FALSE);
                            Yii::$app->queue->push(new DepositJob(['id' => $mod->id]));
                            array_push($success, $row);
                        }
                        array_push($error, $row);
                    }
                    $row++;
                }
                fclose($handle);
            }
        }

        return $this->render(
            'halotel',
            [
                'success' => $success,
                'error'   => $error
            ]
        );
    }
    public function actionHalotelnew()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '3000');
        $success = [];
        $error   = [];
        if (isset($_POST['submit'])) {
            $file = $_FILES['file']['tmp_name'];
            $platform = trim($_POST['platform']);
            //			$file    = "confirmed_payment.csv";
            $success = [];
            $error   = [];
            $row     = 1;
            if (($handle = fopen($file, "r")) !== false) {
                while (($data = fgetcsv($handle, 2000, ",")) !== false) {

                    $transaction_number = trim(isset($data[6]) ? $data[6] : NULL);
                    $phone = trim(isset($data[3]) ? $data[3] : NULL);
                    $business_code = trim(isset($data[1]) ? $data[1] : NULL);
                    $reference = trim(isset($data[7]) ? $data[7] : NULL);
                    $datetime = trim(isset($data[5]) ? $data[5] : NULL);
                    $amount = trim(isset($data[2]) ? $data[2] : NULL);
                    $balance = 0;
                    if (
                        !empty($transaction_number) && !empty($reference)
                        && !empty($datetime) && !empty($phone) && !empty($amount) && is_numeric($amount) && $amount == 1000 && $business_code == $platform
                    ) {
                        $check_if_exists = MpesaPayments::find()->where(['TransID' => $transaction_number])->one();
                        if ($check_if_exists == NULL) {
                            $mod = new MpesaPayments();
                            $mod->id = Uuid::generate()->string;
                            $mod->TransID = $transaction_number;
                            $mod->TransAmount = $amount;
                            $mod->FirstName = NULL;
                            $mod->MiddleName = NULL;
                            $mod->LastName = "Halotel";
                            $mod->MSISDN = $phone;
                            $mod->BillRefNumber = $reference;
                            $mod->OrgAccountBalance = $balance;
                            $mod->created_at = date("Y-m-d H:i:s", strtotime($datetime));
                            $mod->updated_at = date('Y-m-d H:i:s');
                            $mod->save(FALSE);
                            Yii::$app->queue->push(new DepositJob(['id' => $mod->id]));
                            array_push($success, $row);
                        }
                        array_push($error, $row);
                    }
                    $row++;
                }
                fclose($handle);
            }
        }

        return $this->render(
            'halotelnew',
            [
                'success' => $success,
                'error'   => $error
            ]
        );
    }
    public function actionTickets()
    {
        $data = Customer::find()->all();
        foreach ($data as $row) {
            $total = TransactionHistories::countEntry($row->msisdn);
            $row->total = $total;
            $row->updated_at = date("Y-m-d H:i:s");
            $row->save();
        }
    }
    public function actionSetstation()
    {
        Yii::$app->queue->push(new SetStationJob());
    }
    public function actionCorrectairteldate()
    {
        $data = MpesaPayments::find()->where("operator='airtel'")->andWhere("created_at > '2022-07-02'")->all();
        foreach ($data as $row) {
            try {
                $row->created_at = Myhelper::formatAirtelDate($row->TransID);
                $row->updated_at = date("Y-m-d H:i:s");
                $row->save(false);
            } catch (Exception $e) {
            }
        }
    }
    public function actionReference()
    {
        $data = MpesaPayments::find()->where('created_at > "2022-11-01"')->all();
        foreach ($data as $row) {
            if ($row->station_id != NULL) {
                $station = Stations::find()->select(['station_code'])->where(['id' => $row->station_id])->one();
                $model = TransactionHistories::find()->where(['mpesa_payment_id' => $row->id])->one();
                if ($model) {
                    $model->reference_code = $station->station_code;
                    $model->save(false);
                }
                $row->BillRefNumber = $station->station_code;
                $row->save(false);
            }
        }
    }
    public function actionMigrate($created_at, $limit)
    {
        Yii::$app->queue->push(new ArchiveMoneyJob(['created_at' => $created_at, 'limit' => $limit]));
    }
    public function actionArchive($limit)
    {
        $created_at = date("Y-m-d", strtotime("- 2 months"));
        MpesaPayments::archive($created_at, $limit);
    }
    public function actionDownload()
    {
        if (isset($_POST['submit'])) {
            $from = $_POST['from'];
            $to = $_POST['to'];
            $reference = $_POST['reference'];
            if (!empty($from) && !empty($to) && empty($reference)) {
                MpesaPayments::getData($from, $to, $reference);
            }
            if (!empty($from) && !empty($to) && !empty($reference)) {
                MpesaPayments::getData($from, $to, $reference);
            }
        }
        return $this->render('download');
    }


    public function beforeAction($action)
    {
        if (in_array($action->id, array('save', 'insertpayment', 'pay'))) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }
}
