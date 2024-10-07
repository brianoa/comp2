<?php
/*send sms code using queue*/

namespace app\components;

use Yii;
use app\models\SiteReport;
use yii\base\BaseObject;

class TodayReportJob extends BaseObject implements \yii\queue\JobInterface
{
    public function execute($queue)
    {
        //code to send sms by id
        SiteReport::setTodayReport();
    }
}
