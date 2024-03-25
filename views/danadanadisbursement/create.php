<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DanadanaDisbursement */

$this->title = 'Create Danadana Disbursement';
$this->params['breadcrumbs'][] = ['label' => 'Danadana Disbursements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="danadana-disbursement-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
