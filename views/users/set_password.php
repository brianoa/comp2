<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = 'Reset Password';
?>

<div class="users-update">

<?php $form = ActiveForm::begin(); ?>
<?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'value' => $model->first_name, 'readonly' => true]) ?>

<?= $form->field($model, 'last_name')->textInput(['maxlength' => true, 'value' => $model->last_name, 'readonly' => true]) ?>

<?= $form->field($model, 'email')->textInput(['maxlength' => true, 'value' => $model->email, 'readonly' => true]) ?>
<?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'value' => '']) ?>

<div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-block btn-success']) ?>
</div>
<?php $form = ActiveForm::end(); ?>


</div>