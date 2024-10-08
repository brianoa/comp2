<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">


    <p>
        <?= Html::a('Create Users', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= \kartik\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'autoXlFormat'=>true,
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'export'=>[
            'showConfirmAlert'=>false,
            'target'=> \kartik\grid\GridView::TARGET_BLANK
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'first_name',
            'last_name',
            //'national_id',
            //'date_of_birth',
            //'phone_number',
            'email:email',
            //'profile_image',
            #'perm_group',
            [
                'attribute' => 'permgroupname',
                'value'     => 'permgroup.name'
            ],
            //'defaultpermissiondenied',
            //'extpermission',
            //'password',
            //'enabled',
            'created_at',
            //'updated_at',
            //'created_by',

            ['class' => 'yii\grid\ActionColumn',
            'template' => '{view} {update}{delete}{set-password}',
            'buttons' => [
                    'set-password' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-lock"></span>', 
                            ['users/set-password', 'id' => $model->id],
                            [
                                'title' => Yii::t('yii', 'Set Password'),
                            ]
                        );
                    },
                ],
            ],
        ],
        'pjax'=>true,
        'showPageSummary'=>true,
    'toolbar' => [
        '{toggleData}',
                '{export}',
    ],
        'panel'=>[
            'type'=>'default',
           // 'heading'=>'Users'
        ]
    ]); ?>


</div>
