<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ForgotPass;
use yii\helpers\Url;
use app\models\WinningHistories;
use app\models\WinningHistoriesSearch;
use app\models\Users;
use app\models\RevenueReport;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update', 'index', 'logout'],
                'rules' => [
                    [
                        'actions' => ['create', 'update', 'index', 'logout'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!Yii::$app->user->isGuest) {
                                return TRUE;
                            }
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    #'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Fetch daily and monthly revenue data
        $dailyRevenues = RevenueReport::getDailyRevenues();
        $monthlyRevenues = RevenueReport::getMonthlyRevenues();

        // Prepare labels and data for the charts
        $dailyLabels = array_column($dailyRevenues, 'day');
        $dailyData = array_column($dailyRevenues, 'total_revenue');


        $monthlyLabels = array_column($monthlyRevenues, 'month_year');
        $monthlyData = array_column($monthlyRevenues, 'total_revenue');
        $currency = "Tsh ";
        if (isset(Yii::$app->user->identity->perm_group) && Yii::$app->user->identity->perm_group == 3) {
            return $this->redirect(['/transactionhistories/presenter']);
        } else if (isset(Yii::$app->user->identity->perm_group) && Yii::$app->user->identity->perm_group == 6) {
            return $this->redirect(['/winninghistories/index']);
        } else {
            $searchModel = new WinningHistoriesSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '', '', '', '', 1);
            $today_income = \app\models\MpesaPayments::getMpesaCounts('today');
            $today_payout = WinningHistories::getPayout(date("Y-m-d"))['total'];
            $yesterday_payout = \app\models\SiteReport::getSiteReport('yesterday_payout');
            return $this->render('index', [
                'currency' => $currency,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'today_income' => $today_income,
                'today_payout' => $today_payout,
                'yesterday_payout' => $yesterday_payout,
                'dailyLabels' => $dailyLabels,
                'dailyData' => $dailyData,
                'monthlyLabels' => $monthlyLabels,
                'monthlyData' => $monthlyData,
            ]);
        }
    }

    /**
     * Login action.
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout = 'login';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }


        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }
    /**
     * Displays forgot pass page.
     *
     * @return Response|string
     */
    public function actionForgotpass()
    {
        //$this->layout = 'login';
        $model = new ForgotPass();
        $model->passstate = 1;
        $model->scenario = 'sc_email';
        if ($model->load(Yii::$app->request->post())) {
            $userrecord = Users::find()->where(['email' => $model->email])->andWhere('enabled=1')->one();
            if (!$userrecord) {
                Yii::$app->session->setFlash('error', 'Error: Contact Admin!');
                return $this->redirect(['/site/login']);
            } else if ($userrecord && is_null($userrecord->pass_code)) { //generate passcode (OTP)
                $model->processPassCode($userrecord);
                $model->scenario = 'sc_code';
                $model->passstate = 2;
                return $this->render('forgotpass', [
                    'model' => $model,
                ]);
            } else if ($userrecord && in_array($userrecord->pass_state, [2, 3, 4]) && $userrecord->pass_expiry <= date('Y-m-d H:i:s')) { //OTP expiry
                $userrecord->pass_code =  NULL;
                $userrecord->pass_state =  NULL;
                $userrecord->pass_expiry = NULL;
                $userrecord->save(FALSE);
                Yii::$app->session->setFlash('error', 'Error: OTP expired!');
                return $this->redirect(['/site/forgotpass']);
            } else if ($userrecord && in_array($userrecord->pass_state, [2, 3, 4]) && $userrecord->pass_code == $model->passcode) { //Passcode/OTP match
                $model->scenario = 'sc_resetpass';
                $userrecord->pass_state = 6;
                $model->passstate = 6;
                $userrecord->save(FALSE);
                return $this->render('forgotpass', [
                    'model' => $model,
                ]);
            } else if ($userrecord && in_array($userrecord->pass_state, [2, 3]) && $userrecord->pass_code != $model->passcode) { // Passcode/OTP do not match
                $model->addError('passcode', "Wrong code. kindly retry");
                $model->scenario = 'sc_code';
                $model->passstate = $userrecord->pass_state++;
                $model->attempts = $userrecord->pass_state - 1;
                $userrecord->pass_state = $userrecord->pass_state++;
                $userrecord->save(FALSE);
                return $this->render('forgotpass', [
                    'model' => $model,
                ]);
            } else if ($userrecord && $userrecord->pass_state == 4) { //after 3 passcode/OTP attempt
                $userrecord->pass_code =  NULL;
                $userrecord->pass_state =  NULL;
                $userrecord->pass_expiry = NULL;
                $userrecord->enabled = 0;
                $userrecord->save(FALSE);
                Yii::$app->session->setFlash('error', 'Error: Account blocked!');
                return $this->redirect(['/site/forgotpass']);
            } else if ($userrecord && $userrecord->pass_state == 6 && !is_null($model->pass) && $model->pass == $model->confirm_pass) { //Password match
                $model->proceeNewPass($userrecord);
                Yii::$app->session->setFlash('success', 'Success: password reset successfully, login');
                return $this->redirect(['/site/login']);
            } else if ($userrecord && $userrecord->pass_state == 6 && $model->pass != $model->confirm_pass) { //Password no match
                $model->addError('confirm_pass', "Password do not match. kindly retry");
                $model->scenario = 'sc_resetpass';
                $model->passstate = 6;
                return $this->render('forgotpass', [
                    'model' => $model,
                ]);
            } else {
                $userrecord->pass_code =  NULL;
                $userrecord->pass_state =  NULL;
                $userrecord->pass_expiry = NULL;
                $userrecord->save(FALSE);
                return $this->redirect(['/site/forgotpass']);
            }
        }
        return $this->render('forgotpass', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
