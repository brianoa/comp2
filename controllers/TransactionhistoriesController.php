<?php

namespace app\controllers;

use app\components\DepositJob;
use Yii;
use app\models\TransactionHistories;
use app\models\StationShowPresenters;
use app\models\Users;
use app\models\WinningHistories;
use app\models\MpesaPayments;
use app\models\Disbursements;
use app\models\StationShowPrizes;
use app\models\TransactionHistoriesSearch;
use app\models\ProcessedMpesaPayments;
use app\models\StationShows;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\Myhelper;
use app\models\ArchivedTransactionHistories;
use app\models\Customer;
use Webpatser\Uuid\Uuid;
use yii\db\IntegrityException;

/**
 * TransactionhistoriesController implements the CRUD actions for TransactionHistories model.
 */
class TransactionhistoriesController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update', 'index', 'presenter', 'admindraws', 'topplayerdraws', 'jackpotdraw', 'tv', 'tvdraw'],
                'rules' => [
                    [
                        'actions' => ['create', 'update', 'index'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(23));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['presenter'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(39));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['admindraws', 'topplayerdraws'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(41));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['jackpotdraw', 'tv', 'tvdraw'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(43));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['topplayerdraws'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(46));
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
     * Lists all TransactionHistories models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransactionHistoriesSearch();
        $dataProvider = Yii::$app->myhelper->getdataprovider($searchModel);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TransactionHistories model.
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
     * Creates a new TransactionHistories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TransactionHistories();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TransactionHistories model.
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
     * Deletes an existing TransactionHistories model.
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
    public function actionPresenter()
    {
        $presenter = Yii::$app->user->identity;
        $presenter_station_show = StationShowPresenters::presenterStationShow($presenter->id, strtolower(date("l")));
        if ($presenter_station_show) {
            $station_show_id = $presenter_station_show['station_show_id'];
            $start_time = date("Y-m-d") . " " . $presenter_station_show['start_time'];
            $end_time = date("Y-m-d") . " " . $presenter_station_show['end_time'];
            $show_transactions = TransactionHistories::getShowTransactions($station_show_id, $start_time, $end_time);
            $transaction_total = TransactionHistories::getTransactionTotal($station_show_id, $start_time, $end_time)['total'];
            $transaction_count = count($show_transactions);
            $target_achievement = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $show_name = $presenter_station_show['show_name'] . " " . $presenter_station_show['start_time'] . " - " . $presenter_station_show['end_time'];
            $recent_winners = WinningHistories::getRecentWinners($presenter_station_show['station_show_id'], date("Y-m-d"));
            $show_prizes = StationShowPrizes::getShowPrizes(strtolower(date("l")), $presenter_station_show['station_show_id'], date("Y-m-d"));
            $percent_raised = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $percent_pending = round((($presenter_station_show['target'] - $transaction_total) / $presenter_station_show['target']) * 100, 2);
        } else {
            $transaction_total = 0;
            $transaction_count = 0;
            $target_achievement = 0;
            $show_name = "No draw at this moment";
            $recent_winners = array();
            $show_prizes = array();
            $percent_raised = 0;
            $percent_pending = 0;
        }
        //echo json_encode($show_prizes); exit();

        $act = new \app\models\ActivityLog();
        $act->desc = "Presenters page";
        $act->setLog();

        return $this->render('presenter', [
            'from' => date("Y-m-d"),
            'show_name' => $show_name,
            'transaction_total' => $transaction_total,
            'transaction_count' => $transaction_count,
            'target_achievement' => $target_achievement,
            'presenter_station_show' => $presenter_station_show,
            'recent_winners' => $recent_winners,
            'show_prizes' => $show_prizes,
            'percent_raised' => $percent_raised,
            'percent_pending' => $percent_pending
        ]);
    }
    public function actionAdmindraws($show_id = "", $from = "")
    {
        //$presenter=Yii::$app->user->identity;
        $presenter = [];
        $presenter_station_show = [];
        $shows = StationShows::getStationShows();
        if (!empty($show_id) && !empty($from)) {
            //$presenter=StationShowPresenters::getShowAdmin($show_id);
            $presenter_station_show = StationShowPresenters::adminStationShow($show_id, strtolower(date("l", strtotime($from))));
        }
        if (!empty($presenter_station_show)) {
            $station_show_id = $presenter_station_show['station_show_id'];
            $start_time = $from . " " . $presenter_station_show['start_time'];
            $end_time = $from . " " . $presenter_station_show['end_time'];
            $show_transactions = TransactionHistories::getShowTransactions($station_show_id, $start_time, $end_time);
            $transaction_total = TransactionHistories::getTransactionTotal($station_show_id, $start_time, $end_time)['total'];
            $transaction_count = count($show_transactions);
            $target_achievement = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $show_name = $presenter_station_show['show_name'] . " " . $presenter_station_show['start_time'] . " - " . $presenter_station_show['end_time'];
            $recent_winners = WinningHistories::getRecentWinners($presenter_station_show['station_show_id'], $from);
            $show_prizes = StationShowPrizes::getShowPrizes(strtolower(date("l", strtotime($from))), $presenter_station_show['station_show_id'], $from);
            $percent_raised = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $percent_pending = round((($presenter_station_show['target'] - $transaction_total) / $presenter_station_show['target']) * 100, 2);
        } else {
            $transaction_total = 0;
            $transaction_count = 0;
            $target_achievement = 0;
            $show_name = "No draw at this moment";
            $recent_winners = array();
            $show_prizes = array();
            $percent_raised = 0;
            $percent_pending = 0;
        }
        //echo json_encode($show_prizes); exit();

        $act = new \app\models\ActivityLog();
        $act->desc = "Admin Draws";
        $act->setLog();

        return $this->render('admin_draws', [

            'show_id' => $show_id,
            'from' => $from,
            'shows' => $shows,
            'show_name' => $show_name,
            'transaction_total' => $transaction_total,
            'transaction_count' => $transaction_count,
            'target_achievement' => $target_achievement,
            'presenter_station_show' => $presenter_station_show,
            'recent_winners' => $recent_winners,
            'show_prizes' => $show_prizes,
            'percent_raised' => $percent_raised,
            'percent_pending' => $percent_pending
        ]);
    }
    public function actionJackpotdraw($station_id = "", $show_id = "", $from = "", $to = '')
    {
        $today = date("Y-m-d");

        $presenter = [];
        $presenter_station_show = [];
        $shows = StationShows::getJackpotShows();
        if (!empty($show_id) && !empty($from) && !empty($to)) {

            $presenter_station_show = StationShowPresenters::jackpotShow($show_id);
        }
        if (!empty($presenter_station_show)) {
            $station_show_id = $presenter_station_show['station_show_id'];
            $show_transactions = TransactionHistories::getJackpotTransactionsByStation($from, $to, $presenter_station_show['station_id']);
            $transaction_total = TransactionHistories::getJackpotTransactionTotalByStation($from, $to, $presenter_station_show['station_id'])['total'];
            $transaction_count = count($show_transactions);
            $target_achievement = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $show_name = $presenter_station_show['show_name'] . " " . $from . " - " . $to;
            $recent_winners = WinningHistories::getRecentWinners($presenter_station_show['station_show_id'], $today);
            $show_prizes = StationShowPrizes::getShowPrizes(strtolower(date("l", strtotime($today))), $presenter_station_show['station_show_id'], $today);
            $percent_raised = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $percent_pending = round((($presenter_station_show['target'] - $transaction_total) / $presenter_station_show['target']) * 100, 2);
        } else {
            $transaction_total = 0;
            $transaction_count = 0;
            $target_achievement = 0;
            $show_name = "No draw at this moment";
            $recent_winners = array();
            $show_prizes = array();
            $percent_raised = 0;
            $percent_pending = 0;
        }
        //echo json_encode($show_prizes); exit();

        $act = new \app\models\ActivityLog();
        $act->desc = "Jackpot Draw";
        $act->setLog();

        return $this->render('jackpot_draw', [
            'show_id' => $show_id,
            'from' => $from,
            'to' => $to,
            'shows' => $shows,
            'show_name' => $show_name,
            'transaction_total' => $transaction_total,
            'transaction_count' => $transaction_count,
            'target_achievement' => $target_achievement,
            'presenter_station_show' => $presenter_station_show,
            'recent_winners' => $recent_winners,
            'show_prizes' => $show_prizes,
            'percent_raised' => $percent_raised,
            'percent_pending' => $percent_pending
        ]);
    }
    public function actionStationstopdraws($station_id = "", $show_id = "", $from = "", $to = "")
    {
        $today = date('Y-m-d');
        $from = $from ?: $today;
        $to = $to ?: $today;

        if (isset($_GET['criterion'])) {
            switch ($_GET['criterion']) {
                case 'weekly':
                    $from = date('Y-m-d', strtotime('monday this week'));
                    $to = date('Y-m-d', strtotime('sunday this week'));
                    break;
                case 'monthly':
                    $from = date('Y-m-01');
                    $d = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
                    $to = date("Y-m-$d");
                    break;
                case 'range':
                    if (isset($_GET['from']) && isset($_GET['to'])) {
                        $from = $_GET['from'];
                        $to = $_GET['to'];
                        if (strtotime($to) < strtotime($from)) {
                            Yii::$app->session->setFlash('error', 'Error: start date should be before the end date');
                        }
                    } else {
                        $from = date('Y-m-01');
                        $d = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
                        $to = date("Y-m-$d");
                    }
                    break;
                default:
                    break;
            }
        }

        $shows = StationShows::getJackpotShows();
        $presenter_station_show = [];
        if (!empty($show_id)) {
            // $presenter_station_show = StationShowPresenters::adminStationShow($show_id, strtolower(date("l", strtotime($from))));
            $presenter_station_show = StationShowPresenters::jackpotShow($show_id);
        }

        if (!empty($presenter_station_show)) {
            $station_show_id = $presenter_station_show['station_show_id'];
            $show_transactions = TransactionHistories::getJackpotTransactionsByStation($from, $to, $presenter_station_show['station_id']);
            $transaction_total = TransactionHistories::getJackpotTransactionTotalByStation($from, $to, $presenter_station_show['station_id'])['total'];
            $transaction_count = count($show_transactions);
            $target_achievement = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $show_name = $presenter_station_show['show_name'] . " " . $from . " - " . $to;
            $recent_winners = WinningHistories::getRecentWinners($presenter_station_show['station_show_id'], $today);
            $show_prizes = StationShowPrizes::getShowPrizes(strtolower(date("l", strtotime($today))), $presenter_station_show['station_show_id'], $today);
            $percent_raised = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $percent_pending = round((($presenter_station_show['target'] - $transaction_total) / $presenter_station_show['target']) * 100, 2);
        } else {
            $transaction_total = 0;
            $transaction_count = 0;
            $target_achievement = 0;
            $show_name = "No draw at this moment";
            $recent_winners = [];
            $show_prizes = [];
            $percent_raised = 0;
            $percent_pending = 0;
        }

        $act = new \app\models\ActivityLog();
        $act->desc = "Stationstop Draw";
        $act->setLog();

        return $this->render('stationstop_draw', [
            'show_id' => $show_id,
            'from' => $from,
            'to' => $to,
            'shows' => $shows,
            'show_name' => $show_name,
            'transaction_total' => $transaction_total,
            'transaction_count' => $transaction_count,
            'target_achievement' => $target_achievement,
            'presenter_station_show' => $presenter_station_show,
            'recent_winners' => $recent_winners,
            'show_prizes' => $show_prizes,
            'percent_raised' => $percent_raised,
            'percent_pending' => $percent_pending,
        ]);
    }

    public function actionShowtopdraws($show_id = "", $from = "", $to = "")
    {
        $today = date("Y-m-d H:i:s");

        $presenter = [];
        $presenter_station_show = [];
        $shows = StationShows::getNonJackpotShows();
        if (!empty($show_id) && !empty($from) && !empty($to)) {
            $presenter_station_show = StationShowPresenters::adminStationShow($show_id, strtolower(date("l", strtotime($from))));
            // $presenter_station_show = StationShowPresenters::jackpotShow($show_id);
        }
        if (!empty($presenter_station_show)) {
            $station_show_id = $presenter_station_show['station_show_id'];
            $start_time = $from . " " . $presenter_station_show['start_time'];
            $end_time = $to . " " . $presenter_station_show['end_time'];
            $show_transactions = TransactionHistories::getShowTransactions($station_show_id, $start_time, $end_time);
            $transaction_total = TransactionHistories::getTransactionTotal($station_show_id, $start_time, $end_time)['total'];
            $transaction_count = count($show_transactions);
            $target_achievement = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $show_name = $presenter_station_show['show_name'] . " " . $presenter_station_show['start_time'] . " - " . $presenter_station_show['end_time'];
            $recent_winners = WinningHistories::getRecentWinners($presenter_station_show['station_show_id'], $from);
            $show_prizes = StationShowPrizes::getShowPrizes(strtolower(date("l", strtotime($from))), $presenter_station_show['station_show_id'], $from);
            $percent_raised = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $percent_pending = round((($presenter_station_show['target'] - $transaction_total) / $presenter_station_show['target']) * 100, 2);
        } else {
            $transaction_total = 0;
            $transaction_count = 0;
            $target_achievement = 0;
            $show_name = "No draw at this moment";
            $recent_winners = array();
            $show_prizes = array();
            $percent_raised = 0;
            $percent_pending = 0;
        }
        //echo json_encode($show_prizes); exit();

        $act = new \app\models\ActivityLog();
        $act->desc = "Show Top Draw";
        $act->setLog();

        return $this->render('topplayer_draws', [
            'show_id' => $show_id,
            'from' => $from,
            'to' => $to,
            'shows' => $shows,
            'show_name' => $show_name,
            'transaction_total' => $transaction_total,
            'transaction_count' => $transaction_count,
            'target_achievement' => $target_achievement,
            'presenter_station_show' => $presenter_station_show,
            'recent_winners' => $recent_winners,
            'show_prizes' => $show_prizes,
            'percent_raised' => $percent_raised,
            'percent_pending' => $percent_pending
        ]);
    }
    public function actionTvdraw($show_id = "", $from = "", $to = "")
    {
        $today = date("Y-m-d");

        $presenter = [];
        $presenter_station_show = [];
        $shows = StationShows::getJackpotShows();
        if (!empty($show_id) && !empty($from) && !empty($to)) {
            $presenter_station_show = StationShowPresenters::jackpotShow($show_id);
        }
        if (!empty($presenter_station_show)) {
            $station_show_id = $presenter_station_show['station_show_id'];
            $show_transactions = TransactionHistories::getJackpotTransactions($from, $to);
            $transaction_total = TransactionHistories::getJackpotTransactionTotal($from, $to)['total'];
            $transaction_count = count($show_transactions);
            $target_achievement = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $show_name = $presenter_station_show['show_name'] . " " . $from . " - " . $to;
            $recent_winners = WinningHistories::getRecentWinners($presenter_station_show['station_show_id'], $today);
            $show_prizes = StationShowPrizes::getShowPrizes(strtolower(date("l", strtotime($today))), $presenter_station_show['station_show_id'], $today);
            $percent_raised = round(($transaction_total / $presenter_station_show['target']) * 100, 2);
            $percent_pending = round((($presenter_station_show['target'] - $transaction_total) / $presenter_station_show['target']) * 100, 2);
        } else {
            $transaction_total = 0;
            $transaction_count = 0;
            $target_achievement = 0;
            $show_name = "No draw at this moment";
            $recent_winners = array();
            $show_prizes = array();
            $percent_raised = 0;
            $percent_pending = 0;
        }
        //echo json_encode($show_prizes); exit();

        $act = new \app\models\ActivityLog();
        $act->desc = "Tv Draw";
        $act->setLog();

        return $this->render('tv_draw', [
            'show_id' => $show_id,
            'from' => $from,
            'to' => $to,
            'shows' => $shows,
            'show_name' => $show_name,
            'transaction_total' => $transaction_total,
            'transaction_count' => $transaction_count,
            'target_achievement' => $target_achievement,
            'presenter_station_show' => $presenter_station_show,
            'recent_winners' => $recent_winners,
            'show_prizes' => $show_prizes,
            'percent_raised' => $percent_raised,
            'percent_pending' => $percent_pending
        ]);
    }
    public function actionTv($show_id = "", $from = "", $to = "")
    {
        $today = date("Y-m-d");
        $this->layout = 'tv_layout';
        $data = TransactionHistories::getTvTransactions($from, $to);
        $presenter_station_show = StationShowPresenters::jackpotShow($show_id);
        $show_prizes = StationShowPrizes::getShowPrizes(strtolower(date("l", strtotime($today))), $show_id, $today);
        $data = json_encode(explode(",", $data['numbers']));
        return $this->render('tv', [
            'data' => $data,
            'show_id' => $show_id,
            'prize_id' => $show_prizes[0]['prize_id'],
            'presenter_id' => $presenter_station_show['presenter_id'],
            'from' => $from,
            'to' => $to
        ]);
    }
    /**
     * Finds the TransactionHistories model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return TransactionHistories the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TransactionHistories::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionCleaner()
    {
        $data = MpesaPayments::find()->where("state=0")->andWhere("created_at > 2022-05-13")->all();
        foreach ($data as $row) {
            Yii::$app->queue->priority(10)->push(new DepositJob(['id' => $row->id]));
        }
    }
    public function actionAssignshows($limit)
    {
        $data = MpesaPayments::find()->select(['id'])->where(["state" => 0])->orderBy("created_at DESC")->limit($limit)->all();

        foreach ($data as $row) {
            TransactionHistories::processPayment($row->id);
        }
    }
    public function actionArchive($limit)
    {
        $created_at = date("Y-m-d", strtotime("- 2 months"));
        TransactionHistories::archive($created_at, $limit);
    }
    public static function actionRemovedups()
    {
        Myhelper::checkRemoteAddress();
        $dups = TransactionHistories::getDuplicates();
        for ($i = 0; $i < count($dups); $i++) {
            $row = $dups[$i];
            TransactionHistories::removeDups($row['mpesa_payment_id'], $row['total'] - 1);
        }
    }
}
