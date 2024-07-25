<!--+----------------------------------------------------------------------
|| author: Mkinuthia
||  Required Parameters
||
|+-----------------------------------------------------------------------
||         url:  @param string - path to the filter url e.g /mpesapayment/index
||
||        data:  @param array - Parameter passed to the url. Should be passed as key=>value.
||                   e.g ['id'=>$id]
||
|+-----------------------------------------------------------------------
||
||  Optional Parameters
||
|+-----------------------------------------------------------------------
||
||         action:  @param string - url of the form attribute.If not set, the url parameter
||                   will be used as the action.
||
||           from:  The start date of the filter. If not set then will be set to
||                   14 days ago.
||
||             to:  The end date of the filter. If not set then will be set to
||                   the current date.
||
++------------------------------------------------------------------------->

<?php

use app\models\Stations;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$stations = Stations::getFilterstations();
$action = isset($action) && $action != "" ? $action : $url;
$start = isset($from) && $from != "" ? $from : date('Y-m-d', strtotime('-14 days'));
$end = isset($to) && $to != "" ? $to : date('Y-m-d');
$params = $data;
$new_params = '';
$count = 0;
$vt = 0;
$params['criterion'] = "";

foreach ($params as $key => $value) {
    if ($count == 0) {
        if (!is_array($value)) {
            $new_params .= '?';
            $new_params .= $key . '=' . $value;
        } else {
            $vt = 1;
            unset($params[$key]);
        }
    } else {
        if (!is_array($value)) {
            if ($vt == 1) {
                $new_params .= '?' . $key . '=' . $value;
            } else {
                $new_params .= '&' . $key . '=' . $value;
            }
        } else {
            unset($params[$key]);
        }
    }
    $count++;
}
?>
<ul class="nav nav-pills">
    <li role="presentation"
        <?= !isset($_GET['criterion']) || $_GET['criterion'] == 'daily' ? "class='active'" : '' ?>>
        <a href="<?= yii\helpers\Url::base() ?><?= $url ?><?= $new_params ?>&criterion=daily"><i
                class="fa fa-calendar-check-o"></i> Daily</a>
    </li>
    <li role="presentation"
        <?= isset($_GET['criterion']) && $_GET['criterion'] == 'monthly' ? "class='active'" : '' ?>>
        <a href="<?= yii\helpers\Url::base() ?><?= $url ?><?= $new_params ?>&criterion=monthly"><i
                class="fa fa-calendar"></i> Monthly</a>
    </li>
    <li role="presentation"
        <?= isset($_GET['criterion']) && $_GET['criterion'] == 'range' ? "class='active'" : '' ?>>
        <a href="<?= yii\helpers\Url::base() ?><?= $url ?><?= $new_params ?>&criterion=range"><span
                class="glyphicon glyphicon-calendar"></span> Range</a>
    </li>
</ul>
<div class="row">
    <br><br>
    <div class="col-sm-offset-1 col-sm-11">
        <?php
        $params['criterion'] = isset($_GET['criterion']) ? $_GET['criterion'] : 'daily';
        $id = str_replace("/", "", $action);
        echo Html::beginForm(
            yii\helpers\Url::base() . $action,
            'get',
            ['id' => $id, 'class' => 'form form-inline']
        );
        ?>
        <div class="form-group">
            <label for="station">Station:</label>
            <?= Html::dropDownList('station', isset($_GET['station']) ? $_GET['station'] : null, ArrayHelper::map($stations, 'id', 'name'), ['prompt' => 'Select Station', 'class' => 'form-control']) ?>
        </div>

        <?php
        if ($params['criterion'] == 'range') {
            if (isset($_GET['from'])) {
                $from = $_GET['from'];
            } else {
                $from = $start;
            }
            if (isset($_GET['to'])) {
                $to = $_GET['to'];
            } else {
                $to = $end;
            }

            if (Yii::$app->session->hasFlash('error_to_from')) {
                echo '<div class="alert alert-danger">Error: Ensure you select both the start date and the end date</div>';
            }
            ?>

            <div class="form-group">
                <label for="from">From:</label>
                <?= yii\jui\DatePicker::widget([
                    'name' => 'from',
                    'value' => $from,
                    'dateFormat' => 'yyyy-MM-dd',
                    'clientOptions' => ['defaultDate' => '2015-01-01'],
                    'options' => ['class' => 'form-control inmfield required']
                ]) ?>
            </div>

            <div class="form-group">
                <label for="to">To:</label>
                <?= yii\jui\DatePicker::widget([
                    'name' => 'to',
                    'value' => $to,
                    'dateFormat' => 'yyyy-MM-dd',
                    'clientOptions' => ['defaultDate' => '2016-04-01'],
                    'options' => ['class' => 'form-control inmfield required']
                ]) ?>
            </div>

            <?php
            foreach ($params as $key => $value) {
                echo Html::hiddenInput($key, $value);
            }
        } else {
            foreach ($params as $key => $value) {
                echo Html::hiddenInput($key, $value);
            }
        }
        ?>

        <div class="form-group">
            <br>
            <button type="submit" class="form-control btn btn-primary">
                <span class="glyphicon glyphicon-move"></span> Filter
            </button>
        </div>
        <br>
        <?php echo Html::endForm(); ?>
    </div>
</div>
