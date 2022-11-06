<?php
class Arguments extends Exception
{
    public function __construct()
    {
	parent::__construct('Invalid or missed arguments');
    }
};

    require_once('send_email.php');
    
    $mail = 'support@sarus.com';
    $R = $_REQUEST;
    $result = [];
    ToLog('request is '.print_r($_REQUEST,true));
    header('Content-Type: application/json');
    try 
    {
	if( !( isset($R['body']) && isset($R['subject']) ) ) throw new Arguments;
	$result['code'] = 200;
	if( isset($R['body_type']) ) $body_type = $R['body_type'];
	else $body_type = "text";
	$result = send_email($R['subject'],$R['body'],$body_type,$mail,$mail);
    }
    catch(Exception $ex)
    {
	header("HTTP/1.1 400 Bad Request");
	$result['code'] = 400;
	$result['error'] = $ex->getMessage() ? $ex->getMessage() : 'Internal module error';
    }
    echo json_encode($result);
?>