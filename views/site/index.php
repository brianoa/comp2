<?php

/* @var $this yii\web\View */

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Home';
?>
<?php
$dailyLabels = json_encode($dailyLabels);
$dailyData = json_encode($dailyData);
$monthlyLabels = json_encode($monthlyLabels);
$monthlyData = json_encode($monthlyData);
$this->registerCss("
    @keyframes fade {
        0%, 100% { opacity: 0.2; }
        50% { opacity: 1; }
    }
    .loading-dots span {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: currentColor;
        margin: 0 2px;
    }
    .loading-dots span:nth-child(1) { animation: fade 1.5s 0.0s infinite; }
    .loading-dots span:nth-child(2) { animation: fade 1.5s 0.2s infinite; }
    .loading-dots span:nth-child(3) { animation: fade 1.5s 0.4s infinite; }
");
?>
<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-md-3">
                <div class="well well-lg text-dark" style="background-color: #FFFFFF">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format($today_income); ?></h5>
                    <p>Today</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="well well-lg  text-white" style="background-color: #8950FC">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format(app\models\SiteReport::getSiteReport('yesterday')); ?></h5>
                    <p>Yesterday</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="well well-lg text-white" style="background-color: #F64E60">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format(app\models\SiteReport::getSiteReport('last_7_days')) ?></h5>
                    <p>Current Week</p>
                </div>

            </div>
            <div class="col-md-3">
                <div class="well well-lg text-white" style="background-color: #212121">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format(app\models\SiteReport::getSiteReport('currentmonth')) ?></h5>
                    <p>Current month</p>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="well well-lg" style="background-color: #C9F7F5">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format($today_payout); ?></h5>
                    <p>Payouts today</p>
                </div>
                <div class="well well-lg" style="background-color: #FFE2E5">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format($yesterday_payout); ?></h5>
                    <p>Payouts yesterday</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i>
                        Recent Winners
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple" class="table table-striped table-bordered display">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Prize</th>
                                    <th>Reference</th>
                                    <th>Station</th>
                                    <th>S/Show</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataProvider->getModels() as $index => $model) : ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td> <?= $model->prizes ? Html::encode($model->prizes->name) : 'Not Set' ?></td>
                                        <td><?= Html::encode($model->reference_name) ?></td>
                                        <td> <?= $model->stations ? Html::encode($model->stations->name) : 'Not Set' ?></td>
                                        <td> <?= $model->stationshows ? Html::encode($model->stationshows->name) : 'Not Set' ?></td>
                                        <td><?= Html::encode($model->amount) ?></td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="well well-lg" style="background-color: #C9F7F5">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format(app\models\SiteReport::getSiteReport('lastweek')); ?></h5>
                    <p>Last week</p>
                </div>
                <div class="well well-lg" style="background-color: #FFE2E5">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format(app\models\SiteReport::getSiteReport('lastmonth')); ?></h5>
                    <p>Last month</p>
                </div>

                <div class="well well-lg" style="background-color: #E1F0FF">
                    <h5 class="font-weight-bold value-display"><?= $currency; ?> <?= number_format(app\models\SiteReport::getSiteReport('totalrevenue')) ?></h5>
                    <p>Total revenue</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-area me-1"></i>
                        Daily Revenue Chart
                    </div>
                    <div class="card-body">
                        <canvas id="dailyRevenueChart" width="100%" height="40"></canvas>
                        <div id="dailyRevenueData" data-labels='<?= $dailyLabels; ?>' data-data='<?= $dailyData; ?>'></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div id="chartData" data-months='<?= $monthlyLabels; ?>' data-revenues='<?= $monthlyData ?>'>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-1"></i>
                        Monthly Revenue Chart
                    </div>
                    <div class="card-body">
                        <canvas id="myBarChart" width="100%" height="40"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>