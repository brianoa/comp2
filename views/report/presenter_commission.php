<?php
$this->title = 'Presenter Commission';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=row>
    <div class="col-md-12">

        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Show</th>
                    <th>Amount</th>
                    <th>Date</th>
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
                        <td><?= $row['amount']; ?></td>
                        <td><?= $row['created_at']; ?></td>
                    </tr>
                <?php

                }

                ?>
            </tbody>
        </table>
    </div>
</div>