<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TransactionHistoriesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'JACKPOT DRAW';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="transaction-histories-index">
    <div class="row">
        <div class="col-md-12">
            <?php
            $action = '/transactionhistories/jackpotdraw';
            $id = str_replace("/", "", $action);
            echo Html::beginForm(
                $action = yii\helpers\Url::base() . $action,
                $method = 'get',
                $hmtmlOptions = array('class' => 'form form-inline')
            );
            ?>
            <div class="form-group">
                <label for="from">STATION SHOW:&nbsp;&nbsp; </label>
                <?php
                echo Html::dropDownList("show_id", $selection = $show_id, $shows, ['prompt' => '--Select--']);
                ?>
            </div class="form-group">
            <div class="form-group">
                <label for="from">&nbsp;&nbsp; FROM: &nbsp;&nbsp;</label>
                <?= kartik\datetime\DateTimePicker::widget([
                    'name' => 'from',
                    'type' => kartik\datetime\DateTimePicker::TYPE_INPUT,
                    'value' => (!empty($from) ? $from : date('Y-m-d 00:00:00', strtotime('-3 days', time()))),
                    'pluginOptions' => [
                        'autoclose' => true,
                        //'format' => 'yyyy-MM-dd hh:ii'
                    ]
                ]);
                ?>
            </div>
            &nbsp;&nbsp;
            <div class="form-group">
                <label for="to">TO: &nbsp;&nbsp;</label>
                <?= kartik\datetime\DateTimePicker::widget([
                    'name' => 'to',
                    'type' => kartik\datetime\DateTimePicker::TYPE_INPUT,
                    'value' => (!empty($to) ? $to : date("Y-m-d H:i:s")),
                    'pluginOptions' => [
                        'autoclose' => true,
                        //'format' => 'yyyy-MM-dd hh:ii'
                    ]
                ]);
                ?>
            </div>
            &nbsp;&nbsp;
            <div class="form-group">
                <label< /label>
                    <br>
                    <button type="submit" class="form-control btn btn-primary">
                        <span class="glyphicon glyphicon-move"></span> Search &nbsp;&nbsp;
                    </button>
            </div>
            <br>
            <?php echo Html::endform();
            ?>
        </div>

    </div>
    <h1><?= Html::encode(strtoupper($show_name)) ?></h1>
    <div class="row">
        <div class="col-md-6">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-text font-weight-bold"><?= $target_achievement; ?>%</h5>
                    <p class="card-text">Target Achievement</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-dark mb-3">
                <div class="card-body">
                    <h5 class="card-text font-weight-bold"><?= number_format($transaction_count); ?></h5>
                    <p class="card-text">Total Transactions</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-dark bg-light mb-3">
                <div class="card-body">
                    <?php
                    if (!empty($presenter_station_show)) {
                    ?>
                        <button class="btn btn-primary" onclick="runDraw()" type="button">DRAW WINNER</button>
                    <?php
                    }
                    ?>
                    <div id="pie-chart" style="width:100%; height:400px;"></div>
                    <p class="card-text"></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card text-dark bg-light mb-3">
                <div class="card-header">Recent Winners</div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>NAME</th>
                                <th>PHONE</th>
                                <th>REFERENCE</th>
                                <th>PRIZE</th>
                                <th>DATE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            if (count($recent_winners) > 0) {
                                foreach ($recent_winners as $row) {
                            ?>
                                    <tr>
                                        <td><?= $row['reference_name']; ?></td>
                                        <td><?= $row['reference_phone']; ?></td>
                                        <td><?= $row['reference_code']; ?></td>
                                        <td><?= $row['name']; ?></td>
                                        <td><?= $row['created_at']; ?></td>
                                    </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--start of hidden divs -->
    <div id="percent_raised" style="display:none;"><?= $percent_raised; ?></div>
    <div id="percent_pending" style="display:none;"><?= $percent_pending; ?></div>
    <!--end of hidden divs -->

</div>

<!--  draw winner Modal    -->
<div id="draw_winner_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <a class="close" data-dismiss="modal">X</a>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-5">
                            <h4><span id="draw_title"></span></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <input type=hidden name=bu id=bu value="">
                <input type=hidden name=bunit id=bunit value="">

                <div class="container-fluid">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 text-center" id="prizes-grid">
                                <h4><span id="winner_number"> 0 0 0 0 0 0 0 0 0 0 0 0</span></h4>
                                <h4><span id="winner_name"><?= (count($show_prizes) > 0 ? "WAITING FOR DRAW" : "NO DRAWS LEFT FOR PRIZE(S)"); ?></span></h4>
                                <?php
                                for ($i = 0; $i < count($show_prizes); $i++) {
                                    $row = $show_prizes[$i];
                                    $station_show_id = $presenter_station_show['station_show_id'];
                                    $presenter_id = $presenter_station_show['presenter_id'];
                                    $prize_id = $row['prize_id'];
                                ?>
                                    <button id="<?= $row['prize_id']; ?>" class="btn btn-danger" onclick="drawPrize('<?= $station_show_id; ?>','<?= $presenter_id; ?>','<?= $prize_id; ?>','<?= $from; ?>',2,'<?= $to; ?>')" type="button"><?= $row['name']; ?></button>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>