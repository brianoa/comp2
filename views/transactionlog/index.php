<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TransactionLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Transaction Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transaction-log-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Transaction Log', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'json_data:ntext',
            'date',
            'api_type',
            'state',
            //'transID',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
