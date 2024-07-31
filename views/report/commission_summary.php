<?php
$this->title = 'Commission Summary';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-info">
    <div class="panel-heading"> Filters</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <?= $this->renderFile('@app/views/layouts/partials/_date_filter.php', [
                    'data' => [],
                    'url'  => '/report/commissionsummary',
                    'from' => date('Y-m-d')
                ]) ?>
            </div>
        </div>
        <div class="row">
            <?= $this->render('//_notification'); ?>
        </div>
    </div>
</div>
<div class=row>
    <div class="col-md-12">

        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Show</th>
                    <th>Timing</th>
                    <th>Target</th>
                    <th>Achieved</th>
                    <th>Net Revenue</th>
                    <th>Presenter Commission</th>
                    <th>Management Commission</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                ?>
                    <tr>
                        <td><?= $row['station_name']; ?></td>
                        <td><?= $row['show_name']; ?></td>
                        <td><?= $row['show_timing']; ?></td>
                        <td><?= $row['target']; ?></td>
                        <td><?= round($row['achieved']); ?></td>
                        <td><?= round(($row['achieved'] - $row['payout'])); ?></td>
                        <td><?= round($row['presenter_commission']); ?></td>
                        <td><?= round($row['station_commission']); ?></td>
                    </tr>
                <?php

                }

                ?>
            </tbody>
        </table>
    </div>
</div>