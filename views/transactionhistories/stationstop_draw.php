<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TransactionHistoriesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$d = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
$this->title = 'STATION TOP PLAYER DRAW';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="transaction-histories-index">
    <div class="row">
        <div class="col-md-12">
            <?php
            $action = '/transactionhistories/stationstopdraws';
            $id = str_replace("/", "", $action);
            echo Html::beginForm(
                $action = yii\helpers\Url::base() . $action,
                $method = 'get',
                $hmtmlOptions = array('class' => 'form form-inline')
            );
            ?>
            <!-- #region -->
            <div class="panel panel-info w-100">
                <div class="panel-heading"> Filters</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="station">STATION:&nbsp;&nbsp; </label>
                                <?= Html::dropDownList("show_id", $show_id, $shows, ['prompt' => '--Select--', 'class' => 'form-control']) ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="criterion">CRITERION:&nbsp;&nbsp; </label>
                                <?= Html::dropDownList("criterion", null, ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'range' => 'Range'], ['prompt' => '--Select--', 'class' => 'form-control', 'id' => 'criterion-select']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4" id="date-range" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from">FROM:&nbsp;&nbsp; </label>
                                <?= Html::input('date', 'from', $from, ['class' => 'form-control']) ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to">TO:&nbsp;&nbsp; </label>
                                <?= Html::input('date', 'to', $to, ['class' => 'form-control']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 text-right">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-move"></span> Search &nbsp;&nbsp;', ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
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
                                <th></th>
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
                                        <td><button type="button" data-winner-id="<?= $row['id'] ?>" onclick="showDeleteWinnerModal(this)" class="btn btn-primary">WINNER DID NOT PICK</button></td>
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
                                    <button id="<?= $row['prize_id']; ?>" class="btn btn-danger" onclick="drawPrize('<?= $station_show_id; ?>','<?= $presenter_id; ?>','<?= $prize_id; ?>','<?= $from; ?>',4,'<?= $to; ?>')" type="button"><?= $row['name']; ?></button>
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
<!-- winner not picking modal -->
<div id="delete_winner_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <a class="close" data-dismiss="modal">X</a>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-5">
                            <h4><span id="delete_title"></span></h4>
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
                                <h3><span id="winner_number"> This action will delete the winner. Are you sure to want to continue?</span></h3>

                                <button type="button" id="del_winner" class="btn btn-danger" onclick="deleteWinner()">Delete Winner</button>
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
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var criterionSelect = document.getElementById('criterion-select');
        var dateRange = document.getElementById('date-range');

        criterionSelect.addEventListener('change', function() {
            if (this.value === 'range') {
                dateRange.style.display = 'block';
            } else {
                dateRange.style.display = 'none';
            }
        });
    });
</script>