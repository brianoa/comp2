<?php
$this->title = 'Daily Awarding';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-info">
    <div class="panel-heading"> Filters</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <?= $this->renderFile('@app/views/layouts/partials/_reports_date_filter.php', [
                    'data' => [],
                    'url'  => '/report/dailyawarding',
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
                    <th>Prize</th>
                    <th>Timing</th>
                    <th>Awarded</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                $count = count($data);
                for ($i = 0; $i < $count; $i++) {
                    $row = $data[$i];
                    $total += $row['awarded'];
                ?>
                    <tr>
                        <td><?= $row['station_name']; ?></td>
                        <td><?= $row['show_name']; ?></td>
                        <td><?= $row['prize_name']; ?></td>
                        <td><?= $row['show_timing']; ?></td>
                        <td><?= number_format($row['awarded']); ?></td>
                    </tr>
                <?php

                }
                if ($count > 0) {
                ?>
                    <tr>
                        <td class="font-weight-bold" colspan="4">TOTAL</td>
                        <td class="font-weight-bold"><?= number_format($total); ?></td>
                    </tr>
                <?php
                }
                ?>

            </tbody>
        </table>
    </div>
</div>