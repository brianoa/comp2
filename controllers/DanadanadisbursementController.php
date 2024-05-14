<?php

namespace app\controllers;

use app\components\DisburseJob;
use app\components\Myhelper;
use Yii;
use app\models\DanadanaDisbursement;
use app\models\DanadanaDisbursementSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DanadanadisbursementController implements the CRUD actions for DanadanaDisbursement model.
 */
class DanadanadisbursementController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update','index','indexc','toggledisbursement','upload'],
                'rules' => [
                    [
                        'actions' => ['create', 'update','index','indexc','toggledisbursement','upload'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(44) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(44) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['indexc'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(44) );
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
     * Lists all DanadanaDisbursement models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DanadanaDisbursementSearch();
        $dataProvider = Yii::$app->myhelper->getdataprovider($searchModel);
         if(isset($_GET['id']) && isset($_GET['srr']) && $_GET['srr'] == 'failed'){
            $model = $this->findModel($_GET['id']);
            $model->status = 0;
            $model->save(FALSE);
            return $this->redirect('index');
        }
         $act = new \app\models\ActivityLog();
        $act -> desc = "danadana disbursement report";
        $act ->setLog();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DanadanaDisbursement model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggledisbursement(){
        $field         = $_POST['field'];
        $mod           = DanadanaDisbursement::findOne( $_POST['id'] );
        $mod->$field   = $_POST['value'];
        $mod->save( false );
        if($mod->status==0)
        {
            $telco = Myhelper::getOperator($mod->phone_number);
            Yii::$app->queue->push(new DisburseJob(['id'=>$mod->id,'telco' =>$telco]));
        }
    }

    /**
     * Lists all Disbursements models.
     * @return mixed
     */
    public function actionIndexc()
    {
        $searchModel = new DanadanaDisbursementSearch();
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
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new DanadanaDisbursement model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DanadanaDisbursement();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing DanadanaDisbursement model.
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
     * Deletes an existing DanadanaDisbursement model.
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
     * Finds the DanadanaDisbursement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return DanadanaDisbursement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DanadanaDisbursement::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public static function actionRemovedups()
    {
        Myhelper::checkRemoteAddress();
        $dups=DanadanaDisbursement::getDuplicates();
        for($i=0;$i < count($dups); $i++)
        {
            $row=$dups[$i];
            DanadanaDisbursement::removeDups($row['unique_field'],$row['total']-1);
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
                        DanadanaDisbursement::saveDisbursement("",$row->reference_name,$row->phone_number,$row->amount,"management_commission",0,NULL);
                    }
                }
			}
            return $this->redirect(['index']);
		}

		return $this->render( 'upload', [
				'success' => $success,
				'error'   => $error
			]
		);

	}
}
