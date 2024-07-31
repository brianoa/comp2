<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Prizes */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="prizes-form">

    <?php $form = ActiveForm::begin(); ?>


    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'amount')->textInput() ?>

    <?= $form->field($model, 'tax')->textInput() ?>
    <?= $form->field($model, 'disbursable_amount')->textInput() ?>
    <div class="form-group">
        <label>Allow:</label>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'mpesa_disbursement')->checkbox(['label' => 'Mpesa Disbursable?']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'enabled')->checkbox(['label' => 'Enabled']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'enable_tax')->checkbox(['label' => 'Enable Tax']) ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-block btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>