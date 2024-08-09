<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\StationShows */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Station Shows', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="station-shows-view">
    <div class="row">
        <div class="col-sm-4">
            <p>
                <?= Html::a('Update station show', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>

            </p>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    #'id',
                    #'station_id',
                    [
                        'label'  => 'subject_type',
                        'value'  => $model->stations->name,
                    ],
                    'name',
                    'description:ntext',
                    'show_code',
                    'target',
                    'invalid_percentage',
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                    'saturday',
                    'sunday',
                    'start_time',
                    'end_time',
                    'start_date',
                    'end_date',
                    'enabled',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ]) ?>
        </div>
        <?php
        if ((isset($_GET['rs']) && $_GET['rs'] == 1) || !isset($_GET['rs'])) {
        ?>
            <div class="col-sm-7">
                <b><button class="btn btn-success" onclick="presenterModal()"> Add Presenters</button></b>
                <?= kartik\grid\GridView::widget([
                    'moduleId' => 'gridviewKrajee',
                    'dataProvider' => $dataProvider,
                    // 'filterModel' => $searchModel,
                    'columns' => [
                        'fullname',
                        #'is_admin',
                        [
                            'attribute' => 'is_admin',
                            'format'    => 'raw',
                            'value'     => function ($model) {
                                return Html::dropDownList('is_admin' . str_replace('-', '_', $model->id), $model->is_admin, ['0' => 'No', '1' => 'Yes'], [
                                    'prompt'   => '',
                                    "class"    => "form-control ",
                                    'id'       => 'is_admin' . str_replace('-', '_', $model->id),
                                    "onchange" => "saveRecord($(this),'is_admin','$model->id')"
                                ]);
                            },
                        ],

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}',
                            'buttons' => [
                                'delete' => function ($url, $data) {
                                    return Html::a('<span class="glyphicon glyphicon-trash" title="Delete"></span>', ['stationshowpresenters/delete', 'id' => $data->id, 'rs' => 1], ['onClick' => 'return confirm("Are you sure you want to delete this item?")', 'method' => 'post']);
                                },
                            ],
                        ],

                    ],
                ]); ?>
            </div>
        <?php
        }
        if ((isset($_GET['rs']) && $_GET['rs'] == 2)) {
        ?>
            <div class="col-sm-7">
                <b><button class="btn btn-primary" onclick="prizeModal()"> Add Prizes</button></b>
                <?= GridView::widget([
                    'dataProvider' => $prizeDataProvider,
                    // 'filterModel' => $prizeSearchModel,
                    'columns' => [
                        'draw_count',
                        [
                            'attribute' => 'monday',
                            'value'     => 'mondayprize.name'
                        ],
                        [
                            'attribute' => 'tuesday',
                            'value'     => 'tuesdayprize.name'
                        ],
                        [
                            'attribute' => 'wednesday',
                            'value'     => 'wednesdayprize.name'
                        ],
                        [
                            'attribute' => 'thursday',
                            'value'     => 'thursdayprize.name'
                        ],
                        [
                            'attribute' => 'friday',
                            'value'     => 'fridayprize.name'
                        ],
                        [
                            'attribute' => 'saturday',
                            'value'     => 'saturdayprize.name'
                        ],
                        [
                            'attribute' => 'sunday',
                            'value'     => 'sundayprize.name'
                        ],
                        'enabled',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}{update}',
                            'buttons' => [
                                'delete' => function ($url, $data) {
                                    return Html::a('<span class="glyphicon glyphicon-trash" title="Delete"></span>', ['stationshowprizes/delete', 'id' => $data->id, 'rs' => 2], ['onClick' => 'return confirm("Are you sure you want to delete this item?")', 'method' => 'post']);
                                },
                                'update' => function ($url, $data) {
                                    return "<span class='glyphicon glyphicon-pencil' title='edit' onclick='editPrizeModal(\"$data->id\",\"$data->draw_count\",\"$data->monday\",\"$data->tuesday\",\"$data->wednesday\",\"$data->thursday\",\"$data->friday\",\"$data->saturday\",\"$data->sunday\",\"$data->enabled\")'></span>";
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        <?php
        } else if ((isset($_GET['rs']) && $_GET['rs'] == 3)) {
        ?>
            <div class="col-sm-7">
                <b><button class="btn btn-success" onclick="commissionsModal()"> Add Commissions</button></b>
                <?= GridView::widget([
                    'dataProvider' => $commissionsDataProvider,
                    'columns' => [
                        #'perm_group',
                        [
                            'attribute' => 'perm_group',
                            'value'     => 'permgroup.name'
                        ],
                        'commission',
                        'created_at',
                        //'updated_at',
                        //'deleted_at',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}',
                            'buttons' => [
                                'delete' => function ($url, $data) {
                                    return Html::a('<span class="glyphicon glyphicon-trash" title="Delete"></span>', ['stationshowcommissions/delete', 'id' => $data->id, 'rs' => 3], ['onClick' => 'return confirm("Are you sure you want to delete this item?")', 'method' => 'post']);
                                },
                            ],
                        ],

                    ],
                ]); ?>
            </div>
        <?php
        }
        if ((isset($_GET['rs']) && $_GET['rs'] == 4)) {
        }
        ?>
    </div>
</div>

<!-- Modal -->
<?php

echo Html::beginForm(
    $action = Url::to(['/stationshows/addpresenter', 'id' => $model->id, 'rs' => '1']),
    $method = 'post',
    $hmtmlOptions = array('id' => 'addpresenterform')
);
?>
<div class="modal fade" id="presentersModal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Presenter</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
                <div class="col-sm-12">
                    <?= Html::dropDownList('presenter_id', '', \app\models\Users::getUsersList(3), [
                        'prompt'   => '--Select--',
                        'id'       => 'presenterid',
                        'class'    => 'form-control',
                    ]) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-block" id="addpresenterbtn"><span style="font-weight: bold">Add presenter</span></button>
            </div>
        </div>

    </div>
</div>
<?php echo Html::endform(); ?>


<!-- Modal -->
<?php

echo Html::beginForm(
    $action = Url::to(['/stationshows/addprize', 'id' => $model->id, 'rs' => '2']),
    $method = 'post',
    $hmtmlOptions = array('id' => 'addprizeform')
);
?>
<div class="modal fade" id="prizeModal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><span class="addprizespn" style="font-weight: bold">Add prize</span></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
                <div class="col-sm-12">
                    <b>Draw counts</b>
                    <?= Html::hiddenInput('showprizeid', '', ['id' => 'showprizeid']) ?>
                    <?= Html::textInput('draw_count', '', ['class'    => 'form-control', 'id' => 'draw_count']) ?>
                </div>
                <div class="col-sm-12">
                    <b>Monday</b>
                    <?= Html::dropDownList('monday', '', \app\models\Prizes::getPrizesList(), ['prompt' => '--Select--', 'class' => 'form-control', 'id' => 'monday']) ?>
                </div>
                <div class="col-sm-12">
                    <b>Tuesday</b>
                    <?= Html::dropDownList('tuesday', '', \app\models\Prizes::getPrizesList(), ['prompt' => '--Select--', 'class' => 'form-control', 'id' => 'tuesday']) ?>
                </div>
                <div class="col-sm-12">
                    <b>Wednesday</b>
                    <?= Html::dropDownList('wednesday', '', \app\models\Prizes::getPrizesList(), ['prompt' => '--Select--', 'class' => 'form-control', 'id' => 'wednesday']) ?>
                </div>
                <div class="col-sm-12">
                    <b>Thursday</b>
                    <?= Html::dropDownList('thursday', '', \app\models\Prizes::getPrizesList(), ['prompt' => '--Select--', 'class' => 'form-control', 'id' => 'thursday']) ?>
                </div>
                <div class="col-sm-12">
                    <b>Friday</b>
                    <?= Html::dropDownList('friday', '', \app\models\Prizes::getPrizesList(), ['prompt' => '--Select--', 'class' => 'form-control', 'id' => 'friday']) ?>
                </div>
                <div class="col-sm-12">
                    <b>Saturday</b>
                    <?= Html::dropDownList('saturday', '', \app\models\Prizes::getPrizesList(), ['prompt' => '--Select--', 'class' => 'form-control', 'id' => 'saturday']) ?>
                </div>
                <div class="col-sm-12">
                    <b>Sunday</b>
                    <?= Html::dropDownList('sunday', '', \app\models\Prizes::getPrizesList(), ['prompt' => '--Select--', 'class' => 'form-control', 'id' => 'sunday']) ?>
                </div>
                <div class="col-sm-12 mt-3">                    
                    <?= Html::checkbox('enabled', $model->enabled, [
                        'label' => 'Enable',
                        'id' => 'enabled'
                    ]) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-block" id="addprizebtn"><span class="addprizespn" style="font-weight: bold">Add prize</span></button>
            </div>
        </div>

    </div>
</div>
<?php echo Html::endform(); ?>


<!-- Modal -->
<?php

echo Html::beginForm(
    $action = Url::to(['/stationshows/addcommissions', 'id' => $model->id, 'rs' => '3']),
    $method = 'post',
    $hmtmlOptions = array('id' => 'addcommissionsform')
);
?>
<div class="modal fade" id="commissionsModal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Commissions</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
                <div class="col-sm-12">
                    <b>Perm group</b>
                    <?= Html::dropDownList('perm_group', '', \app\models\PermissionGroup::getPermissionGroup(), [
                        'prompt'   => '--Select--',
                        'id'       => 'perm_group',
                        'class'    => 'form-control',
                    ]) ?>
                </div>
                <div class="col-sm-12">
                    <b>Commission</b>
                    <?= Html::textInput('commission', '', ['class'    => 'form-control', 'id' => 'commission']) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-block" id="addcommissionbtn"><span style="font-weight: bold">Add Commission</span></button>
            </div>
        </div>

    </div>
</div>
<?php echo Html::endform(); ?>