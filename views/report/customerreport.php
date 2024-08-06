<?php
$d = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
$this->title = 'Customer Report FROM ' . $start_date . ' TO ' . $end_date;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->renderFile('@app/views/layouts/partials/_date_filter.php', [
            'data' => [],
            'url'  => '/report/customerreport',
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
                    <th>CUSTOMER NAME</th>
                    <th>CUSTOMER PHONE</th>
                    <th>PLAY</th>
                    <th>STATION</th>
                    <!-- <th>DATE</th> -->
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < count($response); $i++) {
                ?>
                    <tr>
                        <td><?= $response[$i]['reference_name']; ?></td>
                        <td><?= $response[$i]['reference_phone']; ?></td>
                        <td><?= $response[$i]['plays']; ?></td>
                        <td><?= $response[$i]['name']; ?></td>
                        <!-- <td><?php //$response[$i]['created_at'];
                                    ?></td> -->
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>