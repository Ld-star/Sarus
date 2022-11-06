<?php
    require_once('clsAPIRequest.php');
    require_once('utils.php');
    require_once('logging.php');
$global_log_file_name = "myfavorites_emails.log";    

function send_email($subject,$message,$type_message,$sender,$to)
{
    if( !($type_message == "text" || $type_message == "html") )$type_message = "text";
    $url = "api.smtp2go.com";
    $port = 443;
    $api_key = "api-5E575B1E569A11EDB417F23C91BBF4A0";
    $sto = [ $to ];
    $data = array('api_key' => $api_key, 'to' => $sto, 'sender' => $sender, 'subject' => $subject, $type_message.'_body' => $message);
    $req = new clsAPIRequest($url,$port,1,"1.1",1);
    $res_api = $req->request("/v3/email/send",$data,'POST');
    $res = ['code'=>200];
    ToLog('Answer: '.print_r($res_api,true));
    switch( $res_api['answer_code'] )
    {
	case 200:
	    $body = json_decode($res_api['body'],true);
	    if( $body['data']['succeeded'] == 1 ) break;;
	    $res['code'] = 400;
	    $res['error'] = $body['data']['failures'][0];
	    ToLog("1 Error sending email to $to");	    
	    header("HTTP/1.1 400 Bad Request");
	    break;
	default:
	    $body = json_decode($res_api['body'],true);
	    $res['code'] = 400;
	    $res['error'] = $body['data']['error'];
	    ToLog("2 Error sending email to $to");
	    header("HTTP/1.1 400 Bad Request");
	    break;
    }
    return $res;
}
?>