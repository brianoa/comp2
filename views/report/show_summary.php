<?php
$d = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
$this->title = 'SHOW SUMMARY FROM ' . $start_date . ' TO ' . $end_date;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->renderFile('@app/views/layouts/partials/_reports_date_filter.php', [
            'data' => [],
            'url'  => '/report/showsummary',
            'from' => date('Y-m-01'),
            'to' => date("Y-m-$d")
        ]) ?>
    </div>
</div>
<div class=row>
    <div class="col-md-12">

        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th>STATION NAME</th>
                    <th>STATION SHOW NAME</th>
                    <th>TOTAL REVENUE</th>
                    <th>TOTAL COMMISSION</th>
                    <th>TOTAL PAYOUTS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < count($response); $i++) {
                ?>
                    <tr>
                        <td><?= $response[$i]['station_name']; ?></td>
                        <td><?= $response[$i]['station_show_name']; ?></td>
                        <td><?= number_format($response[$i]['revenue']); ?></td>
                        <td><?= number_format($response[$i]['commission']); ?></td>
                        <td><?= number_format($response[$i]['payout']); ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>