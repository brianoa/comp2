<?php

namespace app\controllers;

use Yii;
use app\models\WinningHistories;
use app\models\StationShowPresenters;
use app\models\WinningHistoriesSearch;
use app\models\StationShowPrizes;
use app\models\TransactionHistories;
use app\models\Outbox;
use app\models\Disbursements;
use app\components\Myhelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Webpatser\Uuid\Uuid;
use yii\db\IntegrityException;

/**
 * WinninghistoriesController implements the CRUD actions for WinningHistories model.
 */
class WinninghistoriesController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update','index'],
                'rules' => [
                    [
                        'actions' => ['create', 'update','index','notified'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(24) );
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

    /**
     * Lists all WinningHistories models.
     * @return mixed
     */
    public function actionIndex()
    {
        $route = isset($_GET['route'])?$_GET['route']:null;
        $searchModel = new WinningHistoriesSearch();
        $dataProvider = Yii::$app->myhelper->getdataprovider($searchModel);
        $act = new \app\models\ActivityLog();
        $act -> desc = "winninghistories report";
        $act ->setLog();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'route' => $route
        ]);
    }
    /**
     * Method to toggle disbursement
     */
    public function actionNotified(){
        $field         = $_POST['field'];
        $mod           = WinningHistories::findOne( $_POST['id'] );
        $mod->$field   = $_POST['value'];
        $mod->save( false );
    }

    /**
     * Displays a single WinningHistories model.
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
     * Creates a new WinningHistories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new WinningHistories();
        
        
        if ($model->load(Yii::$app->request->post())) {
            $model->id=Uuid::generate()->string;
            $model->status=0;
            $model->station_show_prize_id=$model->prize_id;
            if($model->save())
            {
                return $this->redirect(['create']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function actionDraw()
    {
        $today=date("Y-m-d");
        $response['status']="";
        $response['message']="";
        $response['data']=[];
        $value=Yii::$app->request->post();
        $station_show_id=$value['station_show_id'];
        $presenter_id=$value['presenter_id'];
        $prize_id=$value['prize_id'];
        if(!isset($value['from']) ||!isset($value['admin_draw']))
        {
            $response['status']="fail";
            $response['message']="PLEASE REFRESH PAGE AND TRY AGAIN";
            return \Yii::$app->response->data = json_encode($response);
            
        }
        
        $from=$value['from'];
        $to=$value['to'];
        $admin_draw=$value['admin_draw'];
        //if presenter is not admin drop him
        if($admin_draw==1)
        {
            $presenter_show=StationShowPresenters::adminStationShow($station_show_id,strtolower(date("l",strtotime($from))));
        }
        else if($admin_draw==2)
        {
            $presenter_show=StationShowPresenters::jackpotShow($station_show_id);
        }
        else
        {
            $presenter_show=StationShowPresenters::presenterStationShow($presenter_id,strtolower(date("l")));
        }

        if(!$presenter_show['is_admin'])
        {
            $response['status']="fail";
            $response['message']="PRESENTER MUST BE ADMIN";
        }
        if($admin_draw==2)
        {
            $show_prize=StationShowPrizes::getShowPrize(strtolower(date("l",strtotime($today))),$station_show_id,$prize_id,$today);
        }
        else{
            $show_prize=StationShowPrizes::getShowPrize(strtolower(date("l",strtotime($from))),$station_show_id,$prize_id,$from);
            $plus30=date("H:i",(strtotime($presenter_show['start_time'])+1800));
            if(date("H:i") < $plus30)
            {
                $show_prize=NULL;
            }

        }
        
        if($show_prize)
        {
            //pick a random person
            $past_winners=WinningHistories::distinctWinners($presenter_show['station_id'],$presenter_show['frequency'],date("Y-m-d H:i:s"));
            array_push($past_winners,'1');
            if($admin_draw=="2")
            {
                $transaction_history=TransactionHistories::pickJackpot($past_winners,$from,$to,$presenter_show['station_id']);
            }
            else
            {
                $transaction_history=TransactionHistories::pickRandom($station_show_id,$past_winners,$from);
            }
            
            if($transaction_history)
            {
                try
                {
                    if($show_prize['prizes_given'] < $show_prize['draw_count'])
                    {
                        $draw_count=$show_prize['prizes_given']+1;
                        $unique_field=$draw_count."#".date("Ymd",strtotime($from))."#".$station_show_id."#".$prize_id;
                    }
                    else
                    {
                        $unique_field=$show_prize['prizes_given']."#".date("Ymd",strtotime($from))."#".$station_show_id."#".$prize_id;
                    }
                    $win_key=Uuid::generate()->string;
                    $model=WinningHistories::saveWin($win_key,$prize_id,$transaction_history['reference_name'],$transaction_history['reference_phone']
                            ,$transaction_history['reference_code'],$transaction_history['station_id'],$station_show_id
                        ,$presenter_id,$show_prize['amount'],$unique_field);
                    if($model!=NULL)
                    {
                        
                        if($show_prize['enable_tax'])
                        {
                            $pay_percent=(100-$show_prize['tax']);
                            $to_pay=round(($show_prize['amount']*($pay_percent/100)));
                        }
                        else
                        {
                            $to_pay=$show_prize['amount'];
                        }
                        $dup_check=Disbursements::checkDuplicate($win_key,$transaction_history['reference_phone'],$to_pay);
                        if($dup_check==0 && $admin_draw!=2)
                        {
                            if($show_prize['mpesa_disbursement'])
                            {
                                Disbursements::saveDisbursement($win_key,$transaction_history['reference_name'],$transaction_history['reference_phone'],$to_pay,"winning",0,$transaction_history['station_id']);
                            }
                            else
                            {
                                if(!$show_prize['mpesa_disbursement'] && $show_prize['disbursable_amount'] > 0 )
                                {
                                    $win_key=Uuid::generate()->string;
                                    $unique_field.="extra";
                    $model=WinningHistories::saveWin($win_key,NULL,$transaction_history['reference_name'],$transaction_history['reference_phone']
                            ,$transaction_history['reference_code'],$transaction_history['station_id'],$transaction_history['station_show_id']
                        ,$presenter_id,$show_prize['disbursable_amount'],$unique_field);
                                    Disbursements::saveDisbursement($win_key,$transaction_history['reference_name'],$transaction_history['reference_phone'],$show_prize['disbursable_amount'],"winning",0,$transaction_history['station_id']);
                                }

                            }
                            $draw_count_balance=$show_prize['draw_count']-$show_prize['prizes_given']-1;
                            $transaction_history['draw_count_balance']=$draw_count_balance;
                            $station_name=$presenter_show['station_name'];
                            $arr=[$transaction_history['reference_name'],$show_prize['name'],$station_name];
                            //$message=Myhelper::winningMessage($transaction_history,$show_prize,$station_name);
                            //send an sms
                            Myhelper::setSms('winningMessage',$transaction_history['reference_phone'],$arr,SENDER_NAME,$transaction_history['station_id']);
                        }
                        else
                        {
                            $draw_count_balance=$show_prize['draw_count']-$show_prize['prizes_given'];
                            $transaction_history['draw_count_balance']=$draw_count_balance;
                        }
    
                        $response['status']="success";
                        $response['message']="no message";
                        $response['data']=$transaction_history;
                    }
                }
                catch(IntegrityException $e)
                {
                    $response['status']="fail";
                    $response['message']="DRAW ALREADY DONE FOR THIS PRIZE!";
                }

               
            }
            else{
                $response['status']="fail";
                $response['message']="FAILED TO DRAW! NO TRANSACTION!";
            }
            
        }
        else
        {
            $response['status']="fail";
            $response['message']="NO DRAWS LEFT FOR PRIZE(S)";
        }
        
        \Yii::$app->response->data = json_encode($response);

    }
    /**
     * Updates an existing WinningHistories model.
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
     * Deletes an existing WinningHistories model.
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
     * Finds the WinningHistories model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return WinningHistories the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WinningHistories::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function beforeAction($action)
    {            
        if ($action->id == '') {
            $this->enableCsrfValidation = false;
        }
    
        return parent::beforeAction($action);
    }
}
