<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class AdminAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/admin/admin.css',
        'css/admin/custom.css',
        //'css/bootstrap-tour.css',
        //'css/bootstrap-tour.min.css',
    ];
    public $js = [
        'js/bootstrap-tour-standalone.js',
        'js/bootstrap-tour-standalone.min.js',
        'js/ajax-modal-popup.js',
        'js/jsLib.js',
        'js/dashboard.js',
        'js/globalFunctions.js',
        'js/admin/admin.js',
        //'js/appTour.js',
        'js/highcharts.js',
        'js/custom.js',
        'js/charts.js',
        'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js',
        'js/demo/chart-area-demo.js',
        'js/demo/chart-bar-demo.js',
        'js/demo/chart-pie-demo.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'app\assets\MaskingAsset',
        'app\assets\BowerAssets',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}
