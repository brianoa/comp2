<?php
/*send sms code using queue*/
namespace app\components;
use Yii;
use app\models\Disbursements;
use yii\base\BaseObject;

class DisburseJob extends BaseObject implements \yii\queue\JobInterface
{
    public $id;
    public $telco;
    public function execute($queue)
    {
       
        if(APP_NAME == "mchongo")
        {
            Disbursements::tzPayout($this->id,"mchongo", $this->telco);
        }
        if(APP_NAME == "bomba")
        {
            Disbursements::tzPayout($this->id,"bomba", $this->telco);
        }
        if(APP_NAME == "supa") 
        {
            Disbursements::tzPayout($this->id,"supa", $this->telco);
        }
        
    }

}
?>