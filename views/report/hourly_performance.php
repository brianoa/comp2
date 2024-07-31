<?php
$this->title = 'Hourly Performance';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->renderFile('@app/views/layouts/partials/day_filter.php', [
            'data' => [],
            'url'  => '/report/hourlyperformance',
            'from' => date('Y-m-d')
        ]) ?>
    </div>
</div>
<div class=row>
    <div class="col-md-12">

        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <?php
                    $stations = $response[0];
                    for ($i = 0; $i < count($stations); $i++) {
                    ?><th><?= strtoupper($stations[$i]); ?></th><?php
                                                            }
                                                                ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $k = 0;
                for ($i = 1; $i < count($response); $i++) {
                ?><tr><?php
                        $total = 0;
                        for ($j = 0; $j < count($response[$i]) - 1; $j++) {
                            $total += ($j != 0) ? $response[$i][$j] : 0;
                        ?>

                            <td><?= $response[$i][$j]; ?></td>
                        <?php
                        }

                        ?>
                        <td><?= $total; ?></td>
                    </tr><?php
                        }

                            ?>
            </tbody>
        </table>
    </div>
</div>