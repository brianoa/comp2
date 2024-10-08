<?php

namespace app\controllers;

use Yii;
use app\models\Users;
use app\models\UsersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PermissionGroup;
use app\models\Permission;
use Webpatser\Uuid\Uuid;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update','index','delete','profile'],
                'rules' => [
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(1) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(2) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(3) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                $users = Yii::$app->myhelper->getMembers( array( '' ), array(33) );
                                return in_array( Yii::$app->user->identity->email, $users );
                            }
                        }
                    ],
                    [
                        'actions' => ['profile'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ( ! Yii::$app->user->isGuest ) {
                                return True;
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
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
	public function actionRefreshschema() {
        Yii::$app->db->schema->refresh();
        Yii::$app->cache->flush();
}
    /**
     * Displays a single Users model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $perm=[];
        $model     = $this->findModel($id);
        $groupPerm = PermissionGroup::findOne( $model->perm_group );
        if ( $groupPerm ) {
            $defaultpermarray         = explode( ',', $groupPerm->defaultPermissions );
            $extrapermarray           = explode( ',', $model->extpermission );
            $denieddefaultPermissions = explode( ',', $model->defaultpermissiondenied );
            if ( $model->load( Yii::$app->request->post() ) ) {
                $newpermissions     = $model->extpermission;
                $newdefaultPerm     = array_intersect( $newpermissions, $defaultpermarray );
                $newextrapermission = array_diff( $newpermissions, $newdefaultPerm );

                $newdenied             = array_diff( $defaultpermarray, $newdefaultPerm );
                $denied                = array_filter( $denieddefaultPermissions );
                $prevdeniedbutnowgiven = array_intersect( $newpermissions, $denied );

                $defaultPermissionsdenied = array_merge( $denied, $newdenied );
                $defaultPermissionsdenied = array_diff( $defaultPermissionsdenied, $prevdeniedbutnowgiven );

                $unique                         = array_unique( $defaultPermissionsdenied );
                $model->defaultpermissiondenied = implode( ',', $unique );
                $model->extpermission           = implode( ',', $newextrapermission );
                $model->save(false);
                $act = new \app\models\ActivityLog();
                $act -> desc = "users Edit permission";
                $act -> propts = "'{id:$id }'";
                $act ->setLog();
                        
                Yii::$app->session->setFlash('success', 'Success:  Permissions saved successfully');
                return $this->redirect(['view','id'=>$id]);
            }
            $perm                 = array_merge( $defaultpermarray, $extrapermarray ); // all permissions
            $perm                 = array_diff( $perm, $denieddefaultPermissions ); // Substract denied permissions
            
        }
        $searchModelActivity = new \app\models\ActivityLogSearch();
        $dataProviderActivity = $searchModelActivity->search(Yii::$app->request->queryParams,$id);
        return $this->render('view', [
            'perm' => $perm,
            'dataProviderActivity' =>$dataProviderActivity,
            'model' => $this->findModel($id),
        ]);
    }

     /**
     * Displays a single Users model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionProfile($id)
    {
        $perm=[];
        $model     = $this->findModel($id);
        $groupPerm = PermissionGroup::findOne( $model->perm_group );
        if ( $groupPerm ) {
            $defaultpermarray         = explode( ',', $groupPerm->defaultPermissions );
            $extrapermarray           = explode( ',', $model->extpermission );
            $denieddefaultPermissions = explode( ',', $model->defaultpermissiondenied );
            $perm                 = array_merge( $defaultpermarray, $extrapermarray ); // all permissions
            $perm                 = array_diff( $perm, $denieddefaultPermissions ); // Substract denied permissions
            
        }
        $searchModelActivity = new \app\models\ActivityLogSearch();
        $dataProviderActivity = $searchModelActivity->search(Yii::$app->request->queryParams,$id);
        return $this->render('profile', [
            'perm' => Permission::find()->select('name')->where(['IN','id',$perm])->all(),
            'dataProviderActivity' =>$dataProviderActivity,
            'model' => $this->findModel($id),
        ]);
    }
    
    
    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Users();
        if ($model->load(Yii::$app->request->post())) {
           if(Users::find()->where("email = '$model->email'")->andWhere("perm_group = '{$model->perm_group}'")->one()){
                Yii::$app->session->setFlash('error', 'Error:  Duplicate User');
                return $this->redirect(['create']);
            }else{
                $model->id=Uuid::generate()->string;
                $model->created_at = date('Y-m-d H:i:s');
                $model->created_by = Yii::$app->user->identity->id;
                $model->password = password_hash($model->password, PASSWORD_BCRYPT, array('cost' => 5));
                if($model->save(FALSE)){
                    $act = new \app\models\ActivityLog();
                    $act -> desc = "users create";
                    $act -> propts = "'{id:$model->id }'";
                    $act ->setLog();
                    return $this->redirect(['view', 'id' => $model->id]);
                }else{ 
                    return $this->render('create', [
                        'model' => $model,
                    ]);

                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Users model.
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
            $act -> desc = "users update";
            $act -> propts = "'{id:$model->id }'";
            $act ->setLog();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $act = new \app\models\ActivityLog();
        $act -> desc = "users delete";
        $act -> propts = "'{id:$id }'";
        $act ->setLog();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionSetPassword($id)
    {
        $model = $this->findModel($id);
        if($model->load(Yii::$app->request->post()) ) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            $model->save(false);
            Yii::$app->session->setFlash('success','Password was reset successfully!');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('set_password', ['model' => $model]);
    }
}
