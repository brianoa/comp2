<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ContactSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title= 'Halotel | Confirmed Payments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title text-uppercase"><?= Html::encode($this->title) ?></h4>
    </div>
    <div class="panel-body">
        <div class="container-fluid">
            <div class="row">
                <?php if (count($success) > 0) { ?>
                    <div class="alert alert-success fade show">
                        <p> Data uploaded successfully</p>
                        Saved Lines: <?= " (" . count($success) . ") records in total." ?>
                        <?php //echo implode(",", $success);
                        echo " (" . count($success) . ") records in total." ?> 
                    </div>
                <?php } ?>
                <?php if (count($error) > 0) { ?>
                    <div class="alert alert-danger fade show">
                        Ignored Lines: <?= " (" . count($error) . ") records in total."?>
                        <?php //echo implode(",", $error); ?> 
                    </div>
                <?php } ?>
            </div>
            <div class="row">
                <div class="note note-yellow text-black m-b-15">
                    <div class="note-icon f-s-20">
                        <i class="fa fa-lightbulb fa-2x"></i>
                    </div>
                    <div class="note-content">
                        <h4 class="m-t-5 m-b-5 p-b-2">Hints:</h4>
                        <ul class="m-b-5 p-l-25">
                            <li>The document can be requested from Airtel.</li>
                            <li>The format should be as follows:
                                <ul>
                                    <li>Column[0]-transaction number</li>
                                    <li>Column[8]-reference code</li>
                                    <li>Column[1]-date</li>
                                    <li>Column[6]-phone</li>
                                    <li>Column[7]-amount</li>
                                </ul>
                            </li>
                            <li>Then ensure file is in csv format.</li>
                            <li>Only documents (<strong>CSV</strong>) are allowed.</li>

                        </ul>
                    </div>
                </div>
            </div>

            <?php
            echo Html::beginForm(
                $action = yii\helpers\Url::base() . "/mpesapayments/halotelnew",
                $method = 'post',
                $hmtmlOptions = array(
                    'id'      => 'halotel',
                    'class'   => 'form form-horizontal',
                    'enctype' => 'multipart/form-data'
                )
            );
            ?>
            <div class="form-group ">
                <div class="col-sm-4">
                    <input  class="form-control"type="text" name="platform" placeholder="Enter Platform" value=""/>
                </div>
            </div>
            <div class="form-group field-file ">
                <label class="control-label" for="file">Choose File</label>
                <input type="file" name="file" class="form-control-file" required="required">
                <div class="help-block p-t-10">Example: halotel.csv</div>
            </div>
            <div class="form-group ">
                <div class="col-sm-4">
                    <button type="submit" name="submit" class="form-control btn btn-primary"><i class="fa fa-file-upload"></i> Upload </button>
                </div>
            </div>
            <?php echo Html::endform(); ?>
        </div>
    </div>
</div>