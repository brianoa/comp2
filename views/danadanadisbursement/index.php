<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DisbursementsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danadana Disbursements';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="disbursements-index">
    <div class="panel panel-info">
        <div class="panel-heading"> Filters</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                        <?=$this->renderFile('@app/views/layouts/partials/_date_filter.php', [
                                'data' => [],
                                'url'  => '/danadanadisbursement/index',
                                'from' => date( 'Y-m-d', strtotime( '-42 days' ) )
                        ])?>
                </div>
            </div>
            <div class="row">
                <?= $this->render('//_notification'); ?>  
            </div>
        </div>
    </div>

    <?=  kartik\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'autoXlFormat'=>true,
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'export'=>[
            'showConfirmAlert'=>false,
            'target'=> \kartik\grid\GridView::TARGET_BLANK
        ],
        'pjax'=>true,
        'showPageSummary'=>true,
        'toolbar' => [
            '{toggleData}',
                    '{export}',
        ],
        'panel'=>[
            'type'=>'primary',
            'heading'=>'transactionhistories'
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
           # 'reference_id',
            
           /* [
                'attribute' => 'user',
                'value'     => 'fullname'
            ],
            * 
            */
            'reference_name',
            'phone_number',
            'amount',
            //'conversation_id',
            //'status',
            'disbursement_type',
            //'transaction_reference',
            'created_at',
            [
                 'attribute' => 'status',
                'format'=>'raw',
                'value' => function($model){
                    if(Yii::$app->user->identity->perm_group == 1 && in_array($model->status, [0,2,3])):
                        return Html::dropDownList( 'status' . str_replace('-', '_', $model->id), $model->status, ['0' => 'Pending','2'=>'Failed','3'=>'Retunr','1'=>'Processed'], [
                            'prompt'   => '',
                            "class"    => "form-control ",
                            'id'       => 'status' .str_replace('-', '_', $model->id),
                            "onchange" => "toggleDisbursement($(this),'status','$model->id')"
                        ]);
                    else:
                        if ( $model->status == 1 ) {
                            return "Processed";
                        }else if ( $model->status == 2 ) {
                            return "Failed";
                        } else if ( $model->status == 3 ) {
                            return "Return";
                        } else {
                            return "Pending";
                        }
                    endif;
                },
                'filter'    => array( '0' => 'Pending', '1' => 'Processed','2' =>'Failed','3' =>'return-overpaid' ),        
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
            'heading'=>'Disbursements'
        ]
    ]); ?>


</div>
