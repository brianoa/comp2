<?php

namespace app\controllers;

use Yii;
use app\models\StationShows;
use app\models\StationShowsSearch;
use app\models\StationShowPresenters;
use app\models\StationShowPresentersSearch;
use app\models\StationShowPrizes;
use app\models\StationShowPrizesSearch;
use app\models\StationShowCommissions;
use app\models\StationShowCommissionsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Webpatser\Uuid\Uuid;

/**
 * StationshowsController implements the CRUD actions for StationShows model.
 */
class StationshowsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update', 'index', 'delete', 'addpresenter', 'addprize', 'addcommissions'],
                'rules' => [
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(8));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(9));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(11));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(10));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['addpresenter'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(12));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['addprize'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(14));
                                return in_array(Yii::$app->user->identity->email, $users);
                            }
                        }
                    ],
                    [
                        'actions' => ['addcommissions'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                $users = Yii::$app->myhelper->getMembers(array(''), array(16));
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
     * Lists all StationShows models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StationShowsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StationShows model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $searchModel = new StationShowPresentersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id);

        $prizeSearchModel = new StationShowPrizesSearch();
        $prizeDataProvider = $prizeSearchModel->search(Yii::$app->request->queryParams, $id);

        $commissionsSearchModel = new \app\models\StationShowCommissionsSearch();
        $commissionsDataProvider = $commissionsSearchModel->search(Yii::$app->request->queryParams, $id);

        $act = new \app\models\ActivityLog();
        $act->desc = "stationshows view";
        $act->propts = "'{id:$id }'";
        $act->setLog();

        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'prizeSearchModel' => $prizeSearchModel,
            'prizeDataProvider' => $prizeDataProvider,
            'commissionsSearchModel' => $commissionsSearchModel,
            'commissionsDataProvider' => $commissionsDataProvider
        ]);
    }
    /**
     * 
     */
    public function actionAddpresenter($id, $rs = 1)
    {
        $model = $this->findModel($id);
        if (StationShowPresenters::find()->where("station_show_id =  '$id'")->andWhere("presenter_id = '{$_POST['presenter_id']}'")->one()) {
            Yii::$app->session->setFlash('error', 'Error:  Duplicate presenter');
        } else {
            $ShowPresenter = new StationShowPresenters();
            $ShowPresenter->id = Uuid::generate()->string;
            $ShowPresenter->station_id = $model->stations->id;
            $ShowPresenter->station_show_id = $id;
            $ShowPresenter->presenter_id = $_POST['presenter_id'];
            $ShowPresenter->created_at = date('Y-m-d H:i:s');
            $ShowPresenter->save();
            $act = new \app\models\ActivityLog();
            $act->desc = "stationshow addpresenter";
            $act->propts = "'{id:$ShowPresenter->id }'";
            $act->setLog();
        }
        return $this->redirect(['view', 'id' => $id, 'rs' => $rs]);
    }
    /**
     * 
     */
    public function actionAddprize($id, $rs)
    {
        $model = $this->findModel($id);
        if ($_POST['showprizeid'] == "") :
            $ShowPrize = new StationShowPrizes();
            $ShowPrize->id = Uuid::generate()->string;
        else :
            $ShowPrize = StationShowPrizes::findOne($_POST['showprizeid']);
        endif;

        $ShowPrize->station_id = $model->stations->id;
        $ShowPrize->station_show_id = $id;
        $ShowPrize->draw_count = $_POST['draw_count'];
        $ShowPrize->monday = $_POST['monday'];
        $ShowPrize->tuesday = $_POST['tuesday'];
        $ShowPrize->wednesday = $_POST['wednesday'];
        $ShowPrize->thursday = $_POST['thursday'];
        $ShowPrize->friday = $_POST['friday'];
        $ShowPrize->saturday = $_POST['saturday'];
        $ShowPrize->sunday = $_POST['sunday'];
        $ShowPrize->enabled = $_POST['enabled'];
        $ShowPrize->created_at = date('Y-m-d H:i:s');
        $ShowPrize->save();
        $act = new \app\models\ActivityLog();
        $act->desc = "stationshow addprize";
        $act->propts = "'{id:$ShowPrize->id }'";
        $act->setLog();
        return $this->redirect(['view', 'id' => $id, 'rs' => $rs]);
    }

    /**
     * add commissions
     */
    public function actionAddcommissions($id, $rs = 1)
    {
        $model = $this->findModel($id);
        $ShowCommissions = new StationShowCommissions();
        $ShowCommissions->id = Uuid::generate()->string;
        $ShowCommissions->station_id = $model->stations->id;
        $ShowCommissions->station_show_id = $id;
        $ShowCommissions->perm_group = $_POST['perm_group'];
        $ShowCommissions->commission = $_POST['commission'];
        $ShowCommissions->created_at = date('Y-m-d H:i:s');
        $ShowCommissions->save();
        $act = new \app\models\ActivityLog();
        $act->desc = "stationshow addcommission";
        $act->propts = "'{id:$ShowCommissions->id }'";
        $act->setLog();
        return $this->redirect(['view', 'id' => $id, 'rs' => $rs]);
    }
    /**
     * Creates a new StationShows model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new StationShows();
        $req = Yii::$app->request->post();
        if ($model->load($req)) {
            $model->id = Uuid::generate()->string;
            $model->start_time = !empty($req['StationShows']['start_time']) ? $req['StationShows']['start_time'] : null;
            $model->end_time = !empty($req['StationShows']['end_time']) ? $req['StationShows']['end_time'] : null;
            $model->created_at = date('Y-m-d H:i:s');
            $model->start_date = !empty($req['StationShows']['start_date']) ? $req['StationShows']['start_date'] : null;
            $model->end_date = !empty($req['StationShows']['end_date']) ? $req['StationShows']['end_date'] : null;

            $shows = StationShows::getshows($req['StationShows']['station_id']);
            $new_show = [
                "start_time" => $model->start_time,
                "end_time" => $model->end_time,
                "days" => [
                    "monday" => $model->monday,
                    "tuesday" => $model->tuesday,
                    "wednesday" => $model->wednesday,
                    "thursday" => $model->thursday,
                    "friday" => $model->friday,
                    "saturday" => $model->saturday,
                    "sunday" => $model->sunday
                ]
            ];

            $enabled_days = [
                "monday" => $model->monday,
                "tuesday" => $model->tuesday,
                "wednesday" => $model->wednesday,
                "thursday" => $model->thursday,
                "friday" => $model->friday,
                "saturday" => $model->saturday,
                "sunday" => $model->sunday
            ];

            foreach ($enabled_days as $day => $enabled) {
                if ($enabled == 1 && StationShows::isConflict($new_show, $shows, $day)) {
                    Yii::$app->session->setFlash('error', "Show conflict detected on $day. Could not add show.");
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
            }

            if ($model->save(false)) {
                $act = new \app\models\ActivityLog();
                $act->desc = "stationshow create";
                $act->propts = "'{id:$model->id }'";
                $act->setLog();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing StationShows model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $act = new \app\models\ActivityLog();
            $act->desc = "stationshow update";
            $act->propts = "'{id:$model->id }'";
            $act->setLog();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing StationShows model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $act = new \app\models\ActivityLog();
        $act->desc = "stationshow delete";
        $act->propts = "'{id:$id }'";
        $act->setLog();
        return $this->redirect(['index']);
    }

    /**
     * Finds the StationShows model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return StationShows the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StationShows::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
