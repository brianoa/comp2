<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\StationShows */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="station-shows-form">

    <?php $form = ActiveForm::begin(); ?>


    <?= $form->field($model, 'station_id')->dropDownList(\app\models\Stations::getStations(), ['prompt' => '--Select--']) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'show_code')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'target')->textInput() ?>

    <div class="form-group">
        <label>JACKPOT:</label>
        <div class="row">
            <div class="col-md-3">
                <?= Html::checkbox('jackpot', $model->jackpot, ['id' => 'jackpot', 'label' => 'Enable Jackpot']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'enabled')->checkbox(['label' => 'Enable']) ?>
            </div>
        </div>
    </div>
    <div id="conditionalFields">
        <?= $form->field($model, 'start_date')->widget(\kartik\datetime\DateTimePicker::class, [
            'name' => 'start_date',
            'type' => \kartik\datetime\DateTimePicker::TYPE_INPUT,
            'value' => (!empty($start_date) ? $start_date : date('Y-m-d 00:00:00', strtotime('-3 days', time()))),
            'pluginOptions' => [
                'autoclose' => true,
            ],
        ]) ?>

        <?= $form->field($model, 'end_date')->widget(\kartik\datetime\DateTimePicker::class, [
            'name' => 'end_date',
            'type' => \kartik\datetime\DateTimePicker::TYPE_INPUT,
            'value' => (!empty($end_date) ? $end_date : date('Y-m-d 00:00:00', strtotime('-3 days', time()))),
            'pluginOptions' => [
                'autoclose' => true,
                'forceParse' => false
            ],
        ]) ?>
    </div>

    <?= $form->field($model, 'start_time', ['options' => ['id' => 'starttime']])->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'end_time', ['options' => ['id' => 'endtime']])->textInput(['maxlength' => true]) ?>
    <div class="form-group" id="days-input">
        <label>DAYS:</label>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'monday')->checkbox(['label' => 'Monday']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tuesday')->checkbox(['label' => 'Tuesday']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'wednesday')->checkbox(['label' => 'Wednesday']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'thursday')->checkbox(['label' => 'Thursday']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'friday')->checkbox(['label' => 'Friday']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'saturday')->checkbox(['label' => 'Saturday']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'sunday')->checkbox(['label' => 'Sunday']) ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-block btn-success']) ?>
    </div>
    <?php
    $script = <<< JS
$(document).ready(function() {
    function toggleJackpotFields() {
        if ($('#jackpot').is(':checked')) {
            $('#conditionalFields').show();
            $('#starttime').hide();
            $('#days-input').hide();
            $('#endtime').hide();
        } else {
            $('#conditionalFields').hide();
            $('#starttime').show();
            $('#days-input').show();
            $('#endtime').show();
        }
    }

    toggleJackpotFields();

    $('#jackpot').change(function(event) {
        event.preventDefault();
        toggleJackpotFields();
    });

});
JS;
    $this->registerJs($script);
    ?>
    <?php ActiveForm::end(); ?>

</div>