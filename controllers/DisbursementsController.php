<?php

namespace app\controllers;

use Yii;
use app\models\Disbursements;
use app\models\DisbursementsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Webpatser\Uuid\Uuid;
use app\components\Myhelper;
use app\components\DisburseJob;
use yii\db\IntegrityException;
/**
 * DisbursementsController implements the CRUD actions for Disbursements model.
 */
class DisbursementsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update','index','indexc','toggledisbursement','upload','uploadeddisbursements'],
                'rules' => [
                    [
                        'actions' => ['create', 'update','index','indexc','toggledisbursement','upload'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(29) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(28) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['uploadeddisbursements'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(45) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['indexc'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(30) );
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
     * Lists all Disbursements models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DisbursementsSearch();
        $dataProvider = Yii::$app->myhelper->getdataprovider($searchModel);
         if(isset($_GET['id']) && isset($_GET['srr']) && $_GET['srr'] == 'failed'){
            $model = $this->findModel($_GET['id']);
            $model->status = 0;
            $model->save(FALSE);
            return $this->redirect('index');
        }
         $act = new \app\models\ActivityLog();
        $act -> desc = "disbursement report";
        $act ->setLog();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    } 
    /**
     * Method to toggle disbursement
     */
    public function actionToggledisbursement(){
        $field         = $_POST['field'];
        $mod           = Disbursements::findOne( $_POST['id'] );
        $mod->$field   = $_POST['value'];
        $mod->save( false );
        if($mod->status==0)
        {
            $telco = Myhelper::getOperator($mod->phone_number);
            Yii::$app->queue->push(new DisburseJob(['id'=>$mod->id,'telco' => $telco]));
        }
    }

    /**
     * Lists all Disbursements models.
     * @return mixed
     */
    public function actionIndexc()
    {
        $searchModel = new DisbursementsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        if(isset($_GET['id']) && isset($_GET['srr']) && $_GET['srr'] == 'failed'){
            $model = $this->findModel($_GET['id']);
            $model->status = 0;
            $model->save(FALSE);
        }
        

        return $this->render('indexc', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Disbursements model.
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
     * Creates a new Disbursements model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Disbursements();

        if ($model->load(Yii::$app->request->post()) ) {
            if(trim($model->phone_number)[0]=="0")
            {
                $model->phone_number="254".substr(trim($model->phone_number),1);
            }
            else
            {
                $model->phone_number=trim($model->phone_number);
            }
            //handle amount more than 150k
            Disbursements::saveDisbursement("",$model->reference_name,$model->phone_number,$model->amount,$model->disbursement_type,4,NULL);
            return $this->redirect(['uploadeddisbursements']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Disbursements model.
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
     * Deletes an existing Disbursements model.
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
     * Finds the Disbursements model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Disbursements the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Disbursements::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public static function actionRemovedups()
    {
        Myhelper::checkRemoteAddress();
        $dups=Disbursements::getDuplicates();
        for($i=0;$i < count($dups); $i++)
        {
            $row=$dups[$i];
            Disbursements::removeDups($row['unique_field'],$row['total']-1);
        }
    }
    public function actionUpload() {
		$success = [];
		$error   = [];
		if ( isset( $_POST['submit'] )  && isset($_POST['total']) ) {
			$file = $_FILES['file']['tmp_name'];
			$success = [];
			$error   = [];
			$row     = 1;
			$total=$_POST['total'];
            $arr=[];
			if ( ( $handle = fopen( $file, "r" ) ) !== false ) {
				while ( ( $data = fgetcsv( $handle, 2000, "," ) ) !== false ) {
                    $reference_name=trim(isset($data[0])?$data[0]:NULL);
                    $phone_number=trim(isset($data[1])?$data[1]:NULL);
                    if($reference_name!=null)
                    {
                        $reference_name=Myhelper::removeSpecialChars($reference_name);
                    }
                    if($phone_number!=null)
                    {
                        $phone_number=Myhelper::cleanNumber($phone_number);
                    }
                    $amount=trim(isset($data[2])?$data[2]:NULL);  
					if (!empty($reference_name) && !empty($phone_number)
                    && !empty($amount)  && is_numeric($amount) && is_numeric($phone_number)) {
                        $pay=[
                            "reference_name"=>$reference_name,
                            "phone_number"=>$phone_number,
                            "amount"=>$amount
                        ];
                        array_push($arr,$pay);
                        array_push( $success, $row );
					}
                    else
                    {
                        array_push( $error, $row );
                    }
					$row ++;
				}
				fclose($handle);
                if(count($arr)==$total)
                {
                    foreach($arr as $row)
                    {
                        $row = (object)$row;
                        Disbursements::saveDisbursement("",$row->reference_name,$row->phone_number,$row->amount,"management_commission",4,NULL);
                    }
                }
			}
            return $this->redirect(['uploadeddisbursements']);
		}

		return $this->render( 'upload', [
				'success' => $success,
				'error'   => $error
			]
		);

	}

    public function actionUploadeddisbursements()
    {
        $data = Disbursements::find()->where(["in", "status", ['4','5']])->all();
        return $this->render( 'uploaded_disbursements', 
        [
            'data' => $data
        ]
        );

    }

    public function actionUpdatestatus()
    {
        if (\Yii::$app->request->isAjax) {
            $id = \Yii::$app->request->post('id');
            $status = \Yii::$app->request->post('status');

            $disbursement = Disbursements::findOne($id);

            if ($disbursement) {
                $disbursement->status = $status;
                if ($disbursement->save(false)) {
                    if($disbursement->status == 0){
                        Yii::$app->session->setFlash('success','Record Approved!');
                        $telco = Myhelper::getOperator($disbursement->phone_number);
                        Yii::$app->queue->push(new DisburseJob(['id'=>$disbursement->id, "telco" => $telco]));
                    }else {
                        Yii::$app->session->setFlash('success','Record Rejected!');
                    }
                    
                    return 'Success';
                } else {
                    \Yii::$app->response->statusCode = 500;
                    return 'Error: Unable to save model';
                }
            } else {
                \Yii::$app->response->statusCode = 404;
                return 'Record not found';
            }
        } else {
            \Yii::$app->response->statusCode = 400;
            return 'Error: Invalid request';
        }
    }
    public static function actionRetryfailed()
    {
        $data=Disbursements::find()->where("status=2")->all();
        foreach($data as $row)
        {
            $id=Uuid::generate()->string;
            $row->id=$id;
            $row->status=0;
            $row->save(false);
        }
    }
    public function actionRegenerateid($id)
    {
        $data = Disbursements::findOne($id);
        $data->id=Uuid::generate()->string;
        $data->save(false);
    }
    public function actionDisb($id){
        $model = $this->findModel($id);
        if ($model !== null) {
            $model->delete();
        }
}
}
