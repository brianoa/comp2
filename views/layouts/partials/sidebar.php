<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\icons\Icon;
use luc\tourist\Tourist;

?>
<div class="app-sidebar sidebar-shadow bg-heavy-rain sidebar-text-dark side-bar-toggle">
    <div class="app-header__logo">
        <div class="logo-src"></div>
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <li class="mm-active <?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                <?= Html::a('<i class="metismenu-icon fa fa-home"></i> TOP REPORTS <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>', "#", ['class' => ''])?>               
                
                <ul class="mm-collapse mm-show">
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-home"></i> Home', Url::home(), ['class' => ''])?>
                </li>
                <li class="<?= $presentervisibility?>  <?=$customercarevisibility?>" data-content="Hourly Performance" title="Hourly Performance">
                    <?= Html::a('<i class="metismenu-icon fa fa-clock"></i> Hourly Performance', ['/report/hourlyperformance'], ['class' => ''])?>
                </li>
                <li class="<?= $presentervisibility?>  <?=$customercarevisibility?>" data-content="Show Summary" title="Show Summary">
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> Show Summary', ['/report/showsummary'], ['class' => ''])?>
                </li>
                <li class="<?=$stationmanagementvisibility?> <?=$managementvisibility?> <?=$adminvisibility?> <?=$superadminvisibility?> <?=$customercarevisibility?>" data-content="Live" title="Live Transaction">
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> Live', ['/transactionhistories/presenter'], ['class' => ''])?>
                </li>
                <li class="<?=$stationmanagementvisibility?> <?=$managementvisibility?> <?=$adminvisibility?> <?=$superadminvisibility?> <?=$customercarevisibility?>" data-content="Commissions" title="Commission">
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> Commission Report', ['/report/presentercommission'], ['class' => ''])?>
                </li>
                <li class="<?=$presentervisibility?> <?=$managementvisibility?> <?=$customercarevisibility?>" data-content="Commission Summary" title="Commission Summary">
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> Commission Summary', ['/report/commissionsummary'], ['class' => ''])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$customercarevisibility?>" data-content="Daily Awarding Report" title="Daily Awarding Report">
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> Daily Awarding Report', ['/report/dailyawarding'], ['class' => ''])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$customercarevisibility?>" data-content="Revenue Report" title="Revenue Report">
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> Revenue Report', ['/report/revenue'], ['class' => ''])?>
                 </li> 
                <li class="<?= $presentervisibility?>  <?=$customercarevisibility?>" data-content="Customer report" title="Customer report">
                    <?= Html::a('<i class="metismenu-icon fa fa-clock"></i> Customer report', ['/report/customerreport'], ['class' => ''])?>
                </li> 
                <li class="<?= $presentervisibility?>  <?=$customercarevisibility?>" data-content="Payouts" title="payouts">
                    <?= Html::a('<i class="metismenu-icon fa fa-clock"></i> Payouts', ['/report/payouts'], ['class' => ''])?>
                </li> 
                <li class="<?=$stationmanagementvisibility?> <?=$managementvisibility?> <?=$adminvisibility?> <?= $presentervisibility?>  <?=$customercarevisibility?>" data-content="Admin payout" title="Loser payout">
                    <?= Html::a('<i class="metismenu-icon fa fa-clock"></i> Loser Bonus', ['/report/loserpayout'], ['class' => ''])?>
                </li>  
                <li class="<?=$stationmanagementvisibility?> <?=$managementvisibility?> <?=$adminvisibility?> <?= $presentervisibility?>  <?=$customercarevisibility?>" data-content="Growth trend" title="Growth trend">
                    <?= Html::a('<i class="metismenu-icon fa fa-clock"></i> Growth trends', ['/report/growthtrend'], ['class' => ''])?>
                </li> 
                <li class="<?= in_array( Yii::$app->user->identity->email, Yii::$app->myhelper->getMembers( array( '' ), array(42) ) ) ? '':'hidden'?>  ">
                    <?= Html::a('<i class="metismenu-icon fa fa-clock"></i> Jackpot Draws', ['/transactionhistories/jackpotdraw'], ['class' => ''])?>
                </li> 
                <li class="<?= in_array( Yii::$app->user->identity->email, Yii::$app->myhelper->getMembers( array( '' ), array(42) ) ) ? '':'hidden'?>  ">
                    <?= Html::a('<i class="metismenu-icon fa fa-clock"></i> Tv Draws', ['/transactionhistories/tvdraw'], ['class' => ''])?>
                </li> 
                <li class="<?= in_array( Yii::$app->user->identity->email, Yii::$app->myhelper->getMembers( array( '' ), array(41) ) ) ? '':'hidden'?>  ">
                    <?=Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Presenter Draws', Url::to(['/transactionhistories/admindraws']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= in_array( Yii::$app->user->identity->email, Yii::$app->myhelper->getMembers( array( '' ), array(42) ) ) ? '':'hidden'?>  ">
                    <?=Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Bonus Draws', Url::to(['/bonus/draw']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                </ul>
                </li>

                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>" title="User Administration"
                    data-content="User Management Module. Menu options to manage, edit, view, create users.">
                    <?= Html::a('<i class="metismenu-icon fa fa-users"></i> USER MANAGEMENT <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>', "#", ['class' => ''])?>               
                    <ul>
                    <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a(' List All Users', Url::to(['/users/index']), ['class' => '', 'id' => 'users'])?>
                </li>
                </ul>
                </li>
                
                <li class="<?= $presentervisibility?> <?=$managementvisibility?>  <?=$customercarevisibility?>" title="User Administration"
                    data-content="User Management Module. Menu options to manage, edit, view, create users.">
                    
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> STATION MANAGEMENT <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>', "#", ['class' => ''])?>               
                    <ul>
                    <li class="<?= $presentervisibility?> <?=$managementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-signal"></i> Stations', Url::to(['/stations/index']), ['class' => '', 'id'=>'manage_categories'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-microphone"></i> Station Shows', Url::to(['/stationshows/index']), ['class' => '', 'id'=>'manage_categories'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-gift"></i> Prizes', Url::to(['/prizes/index']), ['class' => '', 'id' => 'newapp'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-gift"></i> Station Management Stations', Url::to(['/stationmanagementstations/index']), ['class' => '', 'id' => 'newapp'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-gift"></i> Station Target', Url::to(['/stationtarget/index']), ['class' => '', 'id' => 'newapp'])?>
                </li>
                    </ul>
                </li>
                
                <li class="<?=$managementvisibility?> " data-content="Application Management Options. Add/Edit/View Application(s) options"
                    title="Application Management">
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> REPORTS <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>', "#", ['class' => ''])?>               
                    <ul>
                    <li class="<?= $presentervisibility?> <?=$managementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Mpesa Report', Url::to(['/mpesapayments/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Transactions Report', Url::to(['/transactionhistories/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> ">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Winners Report',  Url::to(['/winninghistories/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> ">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Winners Report - Pending notifications',  Url::to(['/winninghistories/index','route'=>2]), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Sent SMS',  Url::to(['/sentsms/index','route'=>2]), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Financial Summary', Url::to(['/financialsummaries/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?=$superadminvisibility?> <?=$adminvisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Live Transactions', Url::to(['/transactionhistories/presenter']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?=$superadminvisibility?> <?=$adminvisibility?>  <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Presenter Commission', Url::to(['/report/presentercommission']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Management Commission', Url::to(['/commissions/index','t'=>'m']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$adminvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Disbursements', Url::to(['/disbursements/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= in_array( Yii::$app->user->identity->email, Yii::$app->myhelper->getMembers( array( '' ), array(44) ) ) ? '':'hidden'?> ">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Danadana Disbursements', Url::to(['/danadanadisbursement/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= in_array( Yii::$app->user->identity->email, Yii::$app->myhelper->getMembers( array( '' ), array(44) ) ) ? '':'hidden'?> ">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Danadana Collection', Url::to(['/danadanacollection/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$adminvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Station Target Report', Url::to(['/stationtarget/report']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$adminvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Player Trend Report', Url::to(['/playertrend/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i>(Archived) Mpesa Report', Url::to(['/archived-mpesapayments/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i>(Archived) Transactions Report', Url::to(['/archived-transactionhistories/index']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i>(Archived) Sent SMS',  Url::to(['/archived-sentsms/index','route'=>2]), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                    </ul>
                </li>
                
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?>  <?=$customercarevisibility?>" data-content="Application Management Options. Add/Edit/View Application(s) options"
                    title="Application Management">
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> FINANCE <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>', "#", ['class' => ''])?>               
                    <ul>
                    <li class="<?=$adminvisibility?> <?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Disbursement', Url::to(['/disbursements/create']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?=$adminvisibility?> <?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Upload Disbursement', Url::to(['/disbursements/upload']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?=$adminvisibility?> <?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i> Presenter Disbursement', Url::to(['/commissions/index','t'=>'p']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                <li class="<?=$adminvisibility?> <?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?>  <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-chart-bar"></i>Station manager Disbursement', Url::to(['/commissions/index','t'=>'m']), ['class' => '', 'id' => 'appmenu'])?>
                </li>
                    </ul>
                </li>

                
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>" title=" Module"
                    data-content="Role Based Access Control Management for the applications. Set up Roles and Rules for the different managed Applications"
                >
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> RBAC <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>', "#", ['class' => ''])?>               
                        <ul>
                        <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">                    
                        <?= Html::a('<i class="metismenu-icon fa fa-chalkboard-teacher"></i> Permission Group', Url::to(['/permissiongroup/index', 'type' => \yii\rbac\Item::TYPE_ROLE]), ['class' => ''])?>
                    
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-clipboard-list"></i> Permissions', Url::to(['/permission/index', 'type' => yii\rbac\Item::TYPE_PERMISSION]))?>
                    
                </li>
                        </ul>
                </li>
                
                
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>" title="API Menu" data-content="API Documentation for the system. List and usage of different endpoints.">
                    
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> API <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>', "#", ['class' => ''])?>               
                    <ul>
                    <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fas fa-print"></i> API Documentation', Url::to(['/api-docs']), ['target' => '_blank'])?>
                </li>
                <li class="<?= $presentervisibility?> <?=$managementvisibility?> <?=$stationmanagementvisibility?> <?=$customercarevisibility?>">
                    <?= Html::a('<i class="metismenu-icon fa fa-user-secret"></i> API Users/Applications', Url::to(['/user/api-users']))?>
                </li>
                    </ul>
                </li>
                
                <li class="" title="User Guides/Documentation and FAQs"
                    data-content="This is a link to documents that are associated with this system and frequently asked questions(FAQs)."
                >
                    
                    <?= Html::a('<i class="metismenu-icon fa fa-list"></i> HELP <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>', "#", ['class' => ''])?>               
                    <ul>
                    <li>
                    <a target="_blank" >
                        <i class="metismenu-icon pe-7s-graph2">
                        </i>
                        User Guide
                    </a>
                    <a target="_blank">
                        <i class="metismenu-icon pe-7s-graph2">
                        </i>
                        FAQs
                    </a>
                </li>
                    </ul>
                </li>
               
            </ul>
        </div>
    </div>
</div>
