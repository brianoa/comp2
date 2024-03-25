<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MpesaPaymentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danadana Collection';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mpesa-payments-index">
    <div class="row">
    <div class="panel panel-info col-md-8">
        <div class="panel-heading"> Filters</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                        <?=$this->renderFile('@app/views/layouts/partials/_datetimesec_filter_.php', [
                            'data' => [],
                            'url'  => '/danadanacollection/index',
                            'from' => date( 'Y-m-d H:i:s', strtotime( '-5 hours' ) )
                        ])?>
                </div>
                
            </div>
            <div class="row">
                <?= $this->render('//_notification'); ?>  
            </div>
        </div>
    </div>
    <div class="col-md-4">
                    <div id="" class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-sm-12">
                                    <b>Data upload portal</b>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" style="padding: 10px;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <?php
                                    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
                                    
                                    <div class="row">
                                        <div class="form-group col-sm-6">
                                            <b>CSV</b>  <?= $form->field($model, 'excelfile')->fileInput(['class' => '', 'onchange' => ''])->label('') ?>

                                        </div>
                                        <div class="col-sm-6">
                                            <?php
                                            echo Html::submitButton('Upload Mpesa', ['class' => 'btn btn-primary btn-lg showprogressbar', 'name' => 'submitbtn', 'value' => 'set']); ?>
                                        </div>
                                    </div>
                                    <?php
                                    ActiveForm::end() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
        </div>
    <?= \kartik\grid\GridView::widget([
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
            'heading'=>'Collection'
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

           // 'id',
            'TransID',
            //'FirstName',
            //'MiddleName',
            'LastName',
            'MSISDN',
            //'InvoiceNumber',
            //'BusinessShortCode',
            'ThirdPartyTransID',
            //'TransactionType',
            //'OrgAccountBalance',
            'BillRefNumber',
            'TransAmount',
            'operator',
            //'is_archived',
            'created_at',
            'updated_at',
            //'deleted_at',

        ],
        'pjax'=>true,
        'showPageSummary'=>true,
        'toolbar' => [
            '{toggleData}',
                    '{export}',
        ],
        'panel'=>[
            'type'=>'default',
            'heading'=>$this->title
        ]
    ]); ?>


</div>
