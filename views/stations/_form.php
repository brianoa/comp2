<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stations */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="stations-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'station_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'frequency')->dropDownList(app\models\Valuelist::getValuelistByType('frequency'), ['prompt' => '--Select--']) ?>

    <div class="form-group">
        <label>Enable:</label>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'enabled')->checkbox(['label' => 'Enabled']) ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-block btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>