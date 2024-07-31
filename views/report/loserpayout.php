<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Losers list Limit/Count = ' . $limit;
$this->params['breadcrumbs'][] = $this->title;
?>

<?= Html::beginForm(); ?>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-2" style="text-align: right">
        <?= Html::textInput('limit', ($loadcheck) ? $limit : '', ($loadcheck) ? ['placeholder' => 'Input limit', 'readonly' => 'readonly'] : ['placeholder' => 'Input limit']) ?>
    </div>
    <?php if ($loadcheck) { ?>
        <div class="col-md-2" style="text-align: right">
            <?= Html::textInput('amount', '', ['placeholder' => 'Input AMOUNT to Disburse']) ?>
        </div>
    <?php } ?>
    <div class="col-md-2" style="text-align: left">
        <?= Html::submitButton(($loadcheck) ? 'DISBURSE Payments' : 'LOAD Limit', ['class' => ($loadcheck) ? 'btn btn-success showprogressbar' : 'btn btn-primary showprogressbar']) ?>
    </div>

</div>
<?= Html::endForm() ?>
<div class=row>
    <div class="col-md-12">

        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th>CUSTOMER NAME</th>
                    <th>CUSTOMER PHONE</th>
                    <th>PLAY</th>
                    <!-- <th>STATION</th>-->
                    <!-- <th>DATE</th> -->
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < count($response); $i++) {
                ?>
                    <tr>
                        <td><?= $response[$i]->reference_name; ?></td>
                        <td><?= $response[$i]->reference_phone; ?></td>
                        <td><?= $response[$i]->plays; ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>