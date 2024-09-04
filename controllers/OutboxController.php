<?php

namespace app\controllers;

use app\components\Myhelper;
use app\components\OutboxJob;
use Yii;
use app\models\Outbox;
use app\models\OutboxSearch;
use app\models\SentSms;
use Webpatser\Uuid\Uuid;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
/**
 * OutboxController implements the CRUD actions for Outbox model.
 */
class OutboxController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update','index','bulk','test'],
                'rules' => [
                    [
                        'actions' => ['create', 'update','index','bulk','test'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if(!Yii::$app->user->isGuest){
                                return TRUE;
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
     * Lists all Outbox models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OutboxSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Outbox model.
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
    public function actionTzsms($limit)
    {
        $smses = Outbox::tzOutbox($limit);
        $rows = [];
        $delete = [];
    
        for ($i = 0; $i < count($smses); $i++) {
            $outbox = $smses[$i];
            $rows[] = [$outbox->id, $outbox->receiver, $outbox->sender, $outbox->message, $outbox->station_id, $outbox->created_date, $outbox->category];
            $delete[] = $outbox->id; 
    
            $channel = Myhelper::getSmsChannel($outbox->receiver);
            Myhelper::sendTzSms($outbox->receiver, $outbox->message, SENDER_NAME, $channel, $outbox->id);
        }
    
       
        $columns = ['id', 'receiver', 'sender', 'message', 'station_id', 'created_date', 'category'];
        Yii::$app->sms_db->createCommand()->batchInsert('sent_sms', $columns, $rows)->execute();
    
       
        if (!empty($delete)) {
            $delete_sql = "DELETE FROM outbox WHERE id IN (" . implode(',', array_map('intval', $delete)) . ")";
            \Yii::$app->sms_db->createCommand($delete_sql)->execute();
        }
    }
   

    /**
     * Creates a new Outbox model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Outbox();
        $model->id=Uuid::generate()->string;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->queue->push(new OutboxJob(['id'=>$model->id]));
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Outbox model.
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
     * Deletes an existing Outbox model.
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
     * Finds the Outbox model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Outbox the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Outbox::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionBulk()
    {
        $model = new Outbox();
        if ($model->load(Yii::$app->request->post()) 
        && isset($model->message) 
        && !empty($model->message)) {
            Outbox::insertBulk($model->message);
            return $this->redirect(['outbox/index']);
        }

        return $this->render('bulk', [
            'model' => $model,
        ]);
    }
    public function actionBulky($limit)
    {
        $batch_size=ceil($limit/20);
        for($i=0; $i <20; $i++)
        {
            Outbox::jambobetBatch($batch_size);
        }
    }
    public  function actionRemovedups()
    {
        $dups=Outbox::getDuplicates();
        for($i=0;$i < count($dups); $i++)
        {
            $row=$dups[$i];
            Outbox::removeDups($row['receiver'],$row['total']-4);
        }
    }
    public function actionTuma($date)
    {
        $data=Outbox::find()->where("created_date like '$date%'")->limit(50000)->all();
        foreach($data as $row)
        {
            Yii::$app->queue->push(new OutboxJob(['id'=>$row->id]));
        }
    }
    public function actionHello()
    {
        $sms=['receiver'=>"254728202194",'message'=>"hello world",'id'=>123456];
        $sms=json_decode(json_encode($sms));
       // echo $sms->receiver; exit();
        $result=Outbox::niTextSms($sms);
        var_dump($result);
    }
    /**
	 * Method to check delivery sms
	 */
	public function actionDlr(){
        $headers=getallheaders();
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/json');
        $data = file_get_contents('php://input');
        if($data==NULL)
        {
            return;
        }
        if(!isset($headers['Authorization']) || $headers['Authorization']!=SMPP_TOKEN)
        {
            return;
        }
			$value = json_decode( $data);
			$sentSms = SentSms::findOne($value->message_id);
			if($sentSms != NULL){
				//if success delivered --> move to sentsms and delete it in outbox
                if(in_array($value->status,[1,8])){
                    $sentSms->status=$value->status;
                    $sentSms->save(FALSE);
                }
                // if not delivered and retry time is less 10min --> assign to pending for resend
                if(in_array($value->status,[2,4,16]) && Myhelper::getTimeDiff($sentSms->created_date,date('Y-m-d H:i:s'),'minutes',true) < 10){
                    $sentSms->status=0;
                    $sentSms->save(FALSE);
                }
			}
	}
    public function actionTest($msisdn)
    {
        $channel=Myhelper::getSmsChannel($msisdn);
        $id=Uuid::generate()->string;
        $message=date("YmdHis");
        $res=Myhelper::sendTzSms($msisdn,$message,SENDER_NAME,$channel,$id);
        var_dump($id."#".$msisdn."#".$message);
        var_dump($res);
    }
    public function beforeAction($action)
    {            
        if (in_array($action->id,array('dlr'))) {
            $this->enableCsrfValidation = false;
        }
    
        return parent::beforeAction($action);
    }

}
