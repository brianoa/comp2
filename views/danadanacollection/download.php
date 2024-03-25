<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ContactSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title= 'Download';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title text-uppercase"><?= Html::encode($this->title) ?></h4>
    </div>
    <div class="panel-body">
        <div class="container-fluid">

        
            <?php
            echo Html::beginForm(
                $action = yii\helpers\Url::base() . "/mpesapayments/download",
                $method = 'POST',
                $hmtmlOptions = array(
                    'id'      => 'download',
                    'class'   => 'form form-horizontal',
                    'enctype' => 'multipart/form-data'
                )
            );
            ?>
            <div class="form-group">
                <label for="from">From:</label>
                <?=kartik\datetime\DateTimePicker:: widget([
                                'name' => 'from',
                                'type' => kartik\datetime\DateTimePicker::TYPE_INPUT,
                                'pluginOptions' => [
                                    'autoclose'=>true,
                                    'minuteStep' => 1,
                                    'format' => 'yyyy-mm-dd hh:ii:ss'
                                ]
                            ]);
                    ?>
            </div>

            <div class="form-group">
                <label for="from">To:</label>
                <?=kartik\datetime\DateTimePicker:: widget([
                                'name' => 'to',
                                'type' => kartik\datetime\DateTimePicker::TYPE_INPUT,
                                'pluginOptions' => [
                                    'autoclose'=>true,
                                    'minuteStep' => 1,
                                    'format' => 'yyyy-mm-dd hh:ii:ss'
                                ]
                            ]);
                    ?>
            </div>

            <div class="form-group ">
                <div class="col-sm-4">
                    <input  class="form form-control" type="text" name="reference" placeholder="Enter Reference" value=""/>
                </div>
            </div>

            <div class="form-group ">
                <div class="col-sm-4">
                    <button type="submit" name="submit" class="form-control btn btn-primary"><i class="fa fa-file-upload"></i> Download </button>
                </div>
            </div>
            <?php echo Html::endform(); ?>
        </div>
    </div>
</div>