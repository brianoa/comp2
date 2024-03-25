<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DanadanaDisbursement */

$this->title = 'Update Danadana Disbursement: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Danadana Disbursements', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="danadana-disbursement-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
