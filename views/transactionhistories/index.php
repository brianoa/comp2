<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TransactionHistoriesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Transaction Histories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transaction-histories-index">
    <div class="panel panel-info">
        <div class="panel-heading"> Filters</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <?=$this->renderFile('@app/views/layouts/partials/_datetimesec_filter_.php', [
                        'data' => [],
                        'url'  => '/transactionhistories/index',
                        'from' => date( 'Y-m-d H:i:s', strtotime( '-5 hours' ) )
                    ])?>
                </div>
            </div>
            <div class="row">
                <?= $this->render('//_notification'); ?>  
            </div>
        </div>
    </div>


    <?php
    
           $gridColumns = [['class' => 'yii\grid\SerialColumn'],

           // 'id',Mpesadetails
           # 'mpesa_payment_id',
            'mpesadetails',
            'reference_name',
            'reference_phone',
            'reference_code',
            #'station_id',
            #'station_show_id',
            [
                'attribute' => 'stationname',
                'value'     => 'stations.name'
            ],
            [
                'attribute' => 'stationshowname',
                'value'     => 'stationshows.name'
            ],
            'amount',
            //'status',
            //'is_archived',
            'created_at',
            //'updated_at',
            //'deleted_at',
              // ['class' => '\kartik\grid\ActionColumn','template'=> '{view}', 'urlCreator'=>function(){return '#';}]
        ];
   echo \kartik\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'autoXlFormat'=>true,
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'export'=>[
            'showConfirmAlert'=>false,
            'target'=> \kartik\grid\GridView::TARGET_BLANK
        ],
        'columns' => $gridColumns,
        'pjax'=>true,
    'showPageSummary'=>true,
    'toolbar' => [
        '{toggleData}',
                '{export}',
    ],
    'panel'=>[
        'type'=>'default',
        'heading'=>'transactionhistories'
    ]
    ]); ?>


</div>
