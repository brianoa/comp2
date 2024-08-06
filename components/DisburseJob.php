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
       
        if(in_array(gethostname(),[CMEDIA_COTZ]))
        {
            Disbursements::tzPayout($this->id,"mchongo", $this->telco);
        }
        if(in_array(gethostname(),[TCB]))
        {
            Disbursements::tzPayout($this->id,"tcb", $this->telco);
        }
        if(in_array(gethostname(),[MCHEZOBOMBA]))
        {
            Disbursements::tzPayout($this->id,"bomba", $this->telco);
        }
        if(in_array(gethostname(),[MCHEZOSUPA])) 
        {
            Disbursements::tzPayout($this->id,"supa", $this->telco);
        }
        
    }

}
?>