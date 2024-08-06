<?php
$host_name =gethostname();
define( 'COMP21_COKE','ip-172-31-14-172');
define( 'COMP21_NET','ip-172-31-41-69');
define( 'COMP21_DEV','ip-172-31-46-231');
define( 'CMEDIA_COTZ','ip-172-31-42-43');
define( 'TBC','ip-172-31-47-127');
define( 'EFMTZ_COM','ip-172-31-33-137');
define( 'BANDIKABANDUA','ip-172-31-39-249');
define( 'MCHEZOBOMBA', 'ip-172-31-37-40');
define( 'MCHEZOSUPA', 'ip-172-31-47-127');
define( 'COTZ',[CMEDIA_COTZ,EFMTZ_COM,BANDIKABANDUA,MCHEZOBOMBA,MCHEZOSUPA]);


define( 'NITEXTSMSURL',"https://nitext.co.ke/index.php/api/sendSmsMultiple");
if ( ( in_array($host_name,[COMP21_COKE]))) {
    define( 'PARTYA', '3015585' );
    define( 'REMARKS', 'Remarks' );
    define( 'QUEUETIMEOUTURL',"https://comp21.co.ke/api/disbursement-payment-timeout-result" );
    define( 'RESULTURL',"https://comp21.co.ke/api/disbursement-payment-result-confirmation" );
    define( 'INITIATORNAME',"com21.api" );
    define( 'OCCASION',"Occasion");
    define( 'MPESAPAYMENTREQUESTURL',"https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest");
    define( 'MPESATOKENURL',"https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
}
else if ( ( in_array($host_name,[COMP21_NET]))) {
    define( 'PARTYA', '3015585' );
    define( 'REMARKS', 'Remarks' );
    define( 'QUEUETIMEOUTURL',"https://comp21.net/api/disbursement-payment-timeout-result" );
    define( 'RESULTURL',"https://comp21.net/api/disbursement-payment-result-confirmation" );
    define( 'INITIATORNAME',"com21.api" );
    define( 'OCCASION',"Occasion");
    define( 'MPESAPAYMENTREQUESTURL',"https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest");
    define( 'MPESATOKENURL',"https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
}
else if ( ( in_array($host_name,[COMP21_DEV]))) {
    define( 'PARTYA', '161744' );
    define( 'REMARKS', 'Remarks' );
    define( 'QUEUETIMEOUTURL',"https://comp21.dev/api/disbursement-payment-timeout-result" );
    define( 'RESULTURL',"https://comp21.dev/api/disbursement-payment-result-confirmation" );
    define( 'INITIATORNAME',"pigakazi.api" );
    define( 'OCCASION',"Occasion");
    define( 'MPESAPAYMENTREQUESTURL',"https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest");
    define( 'MPESATOKENURL',"https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
}
 else {
    define( 'PARTYA', '3015585' );
    define( 'REMARKS', 'Remarks' );
    define( 'QUEUETIMEOUTURL',"https://comp21.co.ke/api/disbursement-payment-timeout-result" );
    define( 'RESULTURL',"https://comp21.co.ke/api/disbursement-payment-result-confirmation" );
    define( 'INITIATORNAME',"com21.api" );
    define( 'OCCASION',"Occasion");
    define( 'MPESAPAYMENTREQUESTURL',"https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest");
    define( 'MPESATOKENURL',"https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
 }

?>
