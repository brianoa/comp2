<?php

namespace app\controllers;

use app\components\CommissionJob;
use Yii;
use app\models\Commissions;
use app\models\StationShows;
use app\models\WinningHistories;
use app\models\StationShowCommissions;
use app\models\TransactionHistories;
use app\models\CommissionsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Webpatser\Uuid\Uuid;
use app\components\Myhelper;
/**
 * CommissionsController implements the CRUD actions for Commissions model.
 */
class CommissionsController extends Controller
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
                        'actions' => ['create', 'update','index'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(27) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(26) );
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
     * Lists all Commissions models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CommissionsSearch();
        $dataProvider = Yii::$app->myhelper->getdataprovider($searchModel);
        
        if(isset($_POST['presenter'])){
            $model = $this->findModel($_POST['commmission_id']);
            $presenters = $_POST['presenter'];
            $presenterscount = count($presenters);
            
            $indivudualamount = round($model->amount/$presenterscount);
            //save disbursents
            for($i = 0; $i < $presenterscount; $i ++){
                $pres = \app\models\Users::findOne($presenters[$i]);
                \app\models\Disbursements::saveDisbursement($model->id, $pres->first_name.' '.$pres->last_name, $pres->phone_number, $indivudualamount, 'presenter_commission',0,$model->station_id);   
            }
            Yii::$app->session->setFlash('success', 'Success:  Commission split successfully');
            $model->status = 1;
            $model->save(FALSE);
            
            $act = new \app\models\ActivityLog();
            $act -> desc = "split commission";
            $act -> propts = "'{id:$model->id }'";
            $act ->setLog();
            return $this->redirect(['index','t'=>'p']);
        }
        $act = new \app\models\ActivityLog();
        $act -> desc = "commission report";
        $act ->setLog();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'presenters' => \app\models\Users::findAll(['perm_group'=>3])
        ]);
    }
    public function actionPresenter()
    {
        $searchModel = new CommissionsSearch();
        $dataProvider = Yii::$app->myhelper->getdataprovider($searchModel);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Commissions model.
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
     * Creates a new Commissions model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Commissions();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Commissions model.
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
     * Deletes an existing Commissions model.
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
    public function actionProcess()
    {
        //today processing
        Yii::$app->analyticsqueue->push(new CommissionJob());
    }
    /**
     * Finds the Commissions model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Commissions the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Commissions::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function beforeAction($action)
    {            
        if (in_array($action->id,array('process'))) {
            $this->enableCsrfValidation = false;
        }
    
        return parent::beforeAction($action);
    }
}
