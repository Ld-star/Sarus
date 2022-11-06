<?php
require_once("dtclass.php");

require_once('logging.php');
require_once('api_constants.php');


function echo_boolean($b)
{
    if($b === true) return 'true';
    return 'false';
}

function cds($double,$string)
{
	if( is_null($double) && is_null($string) ) return true;
	if( !is_string($double) ) return false;
	return $double == $string;
}


function set_array_value(&$array,$indexes,$value,$check_existance=false)
{
        $st = &$array;
        $c = count($indexes);
        foreach($indexes as $index=>$i)
        {
                if($index == $c-1 )
                {
                	$is_set = isset( $st[$i] );
                	if( $is_set && $check_existance === false || !$is_set ) $st[$i] = $value;
                        break;
                        
                }
                if( !isset($st[$i]) ) $st[$i] = array();
                else if(!is_array($st[$i]) ) break;
                $stt = &$st[$i];
                unset($st);
                $st = &$stt;
        }
}


function d($time)
{
	return date("Y-m-d",$time);
}

function dd($time)
{
	return date("Y-m-d H:i:s",$time);
}

function int_to_bool($d)
{
	$d++;
	$d--;
	if($d == 0 ) return false;
	return true;
}

function DateToJulianDateTime($date=false)
{
	if($date === false) $date=time();
        $i = getdate($date);
        $m = $i['mon'];
        $d = $i['mday'];
        $y = $i['year'];
        $h = $i['hours'];
        $mn = $i['minutes'];
        $s  = $i['seconds'];
        if ($m > 2) {
                $jy = $y;
                $jm = $m + 1;
        } else {
                $jy = $y - 1;
                $jm = $m + 13;
        }
        $intgr = floor( floor(365.25 * $jy) + floor(30.6001 * $jm) + $d + 1720995);
        // check for switch to Gregorian calendar
        $gregcal = 15 + 31 * (10 + 12 * 1582);
        if ($d + 31 * ($m + 12 * $y) >= $gregcal) {
                $ja = floor(0.01 * $jy);
                $intgr += 2 - $ja + floor(0.25 * $ja);
        }
        // correct for half-day offset
        $dayfrac = $h / 24.0 - 0.5;
        if ($dayfrac < 0.0) {
                $dayfrac += 1.0;
                $intgr--;
        }
  // now set the fraction of a day
        $frac = $dayfrac + ($mn + $s / 60.0) / 60.0 / 24.0;
  // round to nearest second
        $jd0 = ($intgr + $frac) * 100000;
        $jd = floor($jd0);
        if ($jd0 - $jd > 0.5) $jd++;
        return $jd / 100000;
}

function extract_array_by_keys($from,$keys)
{
    $ret = array();
    foreach($keys as $s=>$r) 
    {
	if( is_integer($s) || !isset($from[$s]) ) $s = $r;
	if( isset($from[$s]) ) $ret[$r] = $from[$s];
    }
    return $ret;
}

function handle_api_result($result,$message='')
{
	switch( $result['answer_code'] )
	{
		case S_OK:
		case S_CREATED:
			ToLog('handle_api_result headers are: '.print_r($result['headers'],true));
			ToLog('!! Content-Type="'.$result['headers']['Content-Type'].'"');
			if( $result['headers']['Content-Type'] == 'application/json')
			{
				ToLog('return json');
				$r = json_decode($result['body'],true); check_json( $r, $result['body']);
				return $r['result'];
			}
			else return $result['body'];
			break;
		default:
			throw new Exception('Exception was happen. Details:'.$result['answer_description'].'. Answer body: '.$result['body'].'. Message:'.$message);
			break;
	}		
}

function check_json( $r, $expression )
{
	$code = json_last_error();
	if( $code != JSON_ERROR_NONE )
	{
		$constants = get_defined_constants(true);
		$json_errors = array();
		foreach ($constants["json"] as $name => $value) {
			if (!strncmp($name, "JSON_ERROR_", 11)) $json_errors[$value] = $name;
		}
		throw new Exception('Error in json_decode ['.$json_errors[$code].'] in '.$expression.'. Trace: '.create_debug_string(debug_backtrace()),E_INCORRECT_ARGUMENTS);
	}
}

function ec($str='empty')
{
    ToLog($str.': '.ob_get_level().' contents: '.ob_get_contents());
}

function bts($bool)
{
    return bool_to_string($bool);
}

function bool_to_string($bool)
{
    if($bool === true) return "true";
    if($bool === false) return "false";
    return $bool;
}
function create_debug_string($debug)
{
	$echo = "";
	foreach($debug as $d)
	{
		if($echo != "") $echo .= " -> ";
		$file = substr($d['file'],strrpos($d['file'],"/"));
		$echo .= $file.'('.$d['line'].')';
	}
	return $echo;
}

function array_to_html($arr,$level=0,$finish=false)
{
	$to = 5 * $level;
	$tab = '';
	for($i=0;$i<$to;$i++) $tab .= "&nbsp";
	$str = "";
	if( is_array($arr ) )
	{
		foreach($arr as $key=>$elem)
		{
			$str .= "{$tab}$key: ";
			if(is_array($elem)) 
			{
				$str .= "<br>";
				$str .= array_to_html($elem,$level+1,true);
			}
			else $str .= bool_to_string($elem).'<br>';
		}
//		if( $finish === true ) $str .= "<br>";
	} else $str .= $arr.'<br>';
	return $str;
}
function string_sum($str)
{
	$arr = str_split($str);
	$sum=0;
	foreach($arr as $a)
	{
		$sum += ord($a);
	}
//	ToLog('sum='.$sum);
	return $sum;
}

function remove_trail_zeros($fl)
{
	$fl = $fl.'';
	$l = strlen($fl);
	for($i=$l-1; $i != 0; $i--)
	{
		if($fl[$i]!='0') 
		{
			if($fl[$i]=='.') $i--;
			break;
		}
	}
	if( $i != 0 ) $fl = substr($fl,0,$i+1);
	return $fl;
}

function RemoveSlashes( &$str )
{
	if ( get_magic_quotes_gpc() ) $str = stripslashes($str);		
}

function ifnull( $cond, $def )
{
	if( $cond == '' ) return $def;
	return $cond;
}

function echo_date($before,$d)
{
	return $before.date("Y-m-d",$d);
}

function removeBoundQuotes($str)
{
	$len = strlen($str);
	if( $str[0] == '"' || $str[0] == "'") $from = 1;
	else $from = 0;
	if( $str[$len-1] == '"' || $str[$len-1] == "'" ) $to = $len-1;
	else $to = $len;
	return substr($str,$from,$to-$from);
}

function trim_array($arr)
{
	$ret = array();
	foreach( $arr as $e) $ret[]=trim($e);
	return $ret;
}

function GetDetailsJsonError($jsoncode)
{
	switch($jsoncode)
	{
		case JSON_ERROR_NONE:
			return '';
		case JSON_ERROR_DEPTH:
			return "Stack is overflow"; //Достигнута максимальная глубина стека 	 
		case JSON_ERROR_STATE_MISMATCH:
			return "Incorrect json";
		case JSON_ERROR_CTRL_CHAR:
			return "Managed symbol error"; //Ошибка управляющего символа, возможно неверная кодировка"
		case JSON_ERROR_SYNTAX: 	
			return "Syntax error"; //Синтаксическая ошибка 	 
		case JSON_ERROR_UTF8:
			return "Incorrect UTF8 symbols";
	}
	return "Incorrect error code $jsoncode";
}

function getDateFromMySQL($mysql,$isThrowException=false,$hour=0,$minutes=0)
{
	$preg = "{^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)}";
	if( @preg_match($preg,$mysql,$pockets) ) return mktime($hour,$minutes,0,$pockets[2],$pockets[3],$pockets[1]);
	if( $isThrowException == true )	 throw new Exception('Error preg_match "'.$preg.'" for "'.$mysql.'"');
	return false;
}


function isInteger($cmp)
{
	return is_numeric($cmp) && is_int($cmp+1-1);
}


function _getDateFromMySQL2($mysql,$is_check=true)
{

//   1   2  3
//"2015-01-16"
// 0123456789

	if($is_check === true ) 
	{
		$year = substr($mysql,0,4); $is_year = isInteger($year);
		$month = substr($mysql,5,2); $is_month = isInteger($month);
		$day = substr($mysql,8,2); $is_day = isInteger($day);
		if( $is_year && $is_month && $is_day ) 
		{
			$date = DateTime::createFromFormat('Y-m-d', $mysql);
			$date->setTime(0,0,0,0);	
			$rt = $date->getTimeStamp();
//			ToLog("$mysql _______ $rt");
			return $rt;
		}
		ToLog(__FUNCTION__." failure with argument = $mysql ".create_debug_string(debug_backtrace()));
		return false;
	}
//	ToLog(__FUNCTION__.' '.$mysql);
	$date = DateTime::createFromFormat('Y-m-d', $mysql);
//	$bf = $date->getTimeStamp();
//	ToLog('before '.$bf.' '.d($bf));
	$date->setTime(0,0,0,0);	
	$bf = $date->getTimeStamp();
//	ToLog('after '.$bf.' '.d($bf));
	return $bf;//date->getTimeStamp();	
}


function _getDateFromMySQL($mysql)
{
	$date = DateTime::createFromFormat('Y-m-d', $mysql);
	if($date === false)
	{
	    ToLog(__FUNCTION__." failure with argument = $mysql ".create_debug_string(debug_backtrace()));
	    return false;
	}
	$date->setTime(0,0,0,0);	
	$bf = $date->getTimeStamp();
	return $bf;
}

function getDateTimeFromMySQL($mysql)
{
//   1   2  3  
//"2015-01-16 hh:mm:ss"
// 0123456789012345678
	$year = substr($mysql,0,4);
	$month = substr($mysql,5,2);
	$day = substr($mysql,8,2);
	$seconds=substr($mysql,17,2);
	$hours=substr($mysql,11,2);
	$minutes=substr($mysql,14,2);
	return mktime($hours,$minutes,$seconds,$month,$day,$year);
}


function parse_str_fixed($str,&$arr)
{
	$temp = $str;
	while( $temp != '' )
	{  
		$eq = strpos($temp,'=');
		$amp = strpos($temp,'&');
		//echo "elem=$temp eq=$eq amp=$amp<br>";
		if( $eq !== false )             
		{
			$name = substr( $temp, 0, $eq);
			if( $amp !== false ) 
			{			
				$mean = substr($temp,$eq+1,$amp-$eq-1); 
				$temp = substr($temp,$amp+1);
			}
			else 
			{
				$mean = substr($temp,$eq+1); 
				$temp = '';
			}
			//echo "name=$name mean=$mean<br>";
			$arr[$name] = $mean;
		}
		else break;
	}
}


$scheduler_dt = new DateTimeClass('','EST');
$out_dt = new DateTimeClass('','EST');

function get_scheduler_dt()
{
	global $scheduler_dt;
	return $scheduler_dt->getDefaultTime();
}

function get_out_dt_string()
{
	global $out_dt;
	return $out_dt->getDefaultTimeString();
}

function SQLITE_TIMESTAMP($dt)
{
	return strftime("%Y.%m.%d %H:%M:%S",$dt);
}

function createDir($dir, $isEcho=true)
{
	$lst = glob( $dir, GLOB_ONLYDIR);
//	print_r($lst);
	if( count($lst) == 0 )
	{
		$res = mkdir($dir,0770);
		if( $res === false )
		{       
			if( $isEcho ) echo 'mkdir -> error creating "'.$dir.'"<br>';
			return false;
		}
	}
	return true;
}


function try_to_lock($file_name, $milliseconds=100)
{
	$ct = 0;
	@fclose(fopen($file_name,"a"));
	while(true)
	{
		$file = @fopen($file_name,"r+");
		if( $file == FALSE )
		{
			usleep(1000); // sleep 1 ms
			$ct++;
			if( $ct > $milliseconds ) return false;
		}
		else break;
	}
	flock( $file, LOCK_EX );
	return $file;
}

function in_array_subsr($what, $lst )
{
	foreach ( $lst as $item)
	{
		if( strpos($item, $what) !== false) return true;
	}
	return false;
}

function delete_zero_files( $lstfiles)
{
	$ret = false;
	foreach( $lstfiles as $item)
	{
		if( filesize( $item ) == 0 ) 
		{
			$ret = true;
			unlink($item);
		}  
	}	
	return $ret;
}

function handled_copy( $from, $to ) 
{
	ob_start();
	$r = copy($from, $to);
	$st = ob_get_contents();
	ob_end_clean();
	if( $r == true ) return true;
	if( preg_match('{\\]:(.+)\\sin\\s}',$st,$pockets) )		
	{
		echo "error in handler_copy($from,$to) :  ".$pockets[1]."<br>";
	}
	else
	{
		echo "error in handler_copy($from,$to) :  ".$st."<br>";
	}
	return false;
}

function exec_script($url, $params = array())
{
    $parts = parse_url($url);
    if( isset($params['PHPSESSID']) ) unset($params['PHPSESSID']);
/*
    require_once('clsAPIRequest.php');
    $r = new clsAPIRequest($parts['host']);
    $res = $r->request('/adm_gui/dn.php',$params,'POST',array(),true);
    ToLog('clsAPIREquest:result = '.print_r($res,true));
    return;*/
    if( isset($params['PHPSESSID']) ) unset($params['PHPSESSID']);
    $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80);
    if ($fp == FALSE )
    {
	echo 'input parameters are:<br>url -> '.$url.'<br>params -> '.print_r($params,true).'<br>Parsed URL array -> '.print_r($parts,true);
        return false;
    }
 
    $data = http_build_query($params, '', '&');
    ToLog(__FUNCTION__.' url='.$url.' data is: '.$data);
//	echo 'exec_script: url -> '.$url.' $params -> '.$data.'<br>';
	
    fwrite($fp, "POST " . (!empty($parts['path']) ? $parts['path'] : '/') . " HTTP/1.1\r\n");
    fwrite($fp, "Host: " . $parts['host'] . "\r\n");
    fwrite($fp, "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20100101 Firefox/21.0\r\n".
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n".
				"Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3\r\n".
				"Accept-Encoding: gzip, deflate\r\n");
    fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
    fwrite($fp, "Content-Length: " . strlen($data) . "\r\n");
    fwrite($fp, "Connection: Close\r\n\r\n");
    fwrite($fp, $data);
    fclose($fp);
 
    return true;
}

function StrToSpan($str,$color)
{
	return "<span style='color:$color'>".$str."</span>";
}
function StrToSpanCB($str,$c='yellow',$b='black')
{
	return "<span style='color:$c; background-color:$b;'>".$str."</span>";
}

/*
function GetCurrentPath($divider)
{
	$current_path = getcwd();
	if($current_path === false ) $current_path='';
	else if( !preg_match('{'.$divider.'$}',$current_path ) ) $current_path .= $divider;
//	echo '<br>'.$current_path.'<br>';
	return $current_path;
}*/

function GetCurrentPath($divider,$file = __FILE__)
{
	$data = pathinfo($file);
	return $data['dirname'].$divider;	
}

function GetCurrentFile( $file )
{
	$data = pathinfo($file);
	return $data['basename'];
}

function print_array($a,$br='<br>')
{
	foreach($a as $elem)
	{
		echo $elem.$br;
	}
}

function isFileExists($fn)
{
	clearstatcache();
	return file_exists($fn);
}


function eb($r)
{
	if( $r === true ) return 'true';
	return 'false';
}

function EchoBoolean($r)
{
	if( $r === true ) return 'true';
	return 'false';
}

function isNewLine($symbol)
{
	$code = ord($symbol);
	return $code == 10 || $code == 13 || $code == 0;
}

function isDigit($symbol)
{	
	return $symbol >= '0' && $symbol <= '9';
}

function cia( $ar, $key, $value)
{
	if( isset($ar[$key]) ) return $ar[$key]==$value;
	return $value == "";
}


function handle_result($r, $error_message='Internal error' )
{
	if( is_bool($r) && $r === true ) $res = 'var res = { success: true };';
	else 
	{
		$res = 'var res = { success: false, error:"'.$error_message.'" };';
		require_once('logging.php');
		ToLog($error_message);
	}
	return $res;
}
function handle_xml_result($r, $names="empty", $body="", $rec_count=0, $error_message = 'Internal error')
{
	header('Content-Type: text/xml');
	if( is_bool($r) && $r === true ) $res = "<$names><success>true</success><total>$rec_count</total>$body</$names>";
	else {
		require_once('logging.php');
		$res = "<$names><success>false</success><total>0</total><error>$error_message</error></$names>";
		ToLog($error_message);
	}
	return '<?xml version="1.0" encoding="UTF-8"?>'.$res;
}


class Timer
{
	protected $from_time;
	public function __construct()
	{
		$this->from_time = microtime(true);
	}
	public function ch()
	{
		$time = microtime(true);
		$ret = $time - $this->from_time;
		$this->from_time = $time;
		return $ret;
	}
	protected $tim;
	protected $ar_times = array();
	
	public function set($name)
	{
		$this->ar_times[$name] = microtime(true);
	}
	public function get($name)
	{
		if( !isset($this->ar_times[$name]) ) return false;
		return (microtime(true) - $this->ar_times[$name]);
	}		
};

	function isNewReferenceStyle()
	{
		$res = preg_match("{^(\\d+)\\.(\\d+)}",PHP_VERSION,$pockets);
		if( $res > 0 )
		{                   	
			if( $pockets[1] > 5 || $pockets[1]==5 && $pockets[2] >= 4 ) return true;
		}
		return false;
	}


function sendErrorMail($message,$EMail)
{
	$isSuccess = true;
	$fromEmail = "resetphones@gmail.com";
	$Headers = 
"From: Users support$fromEmail\n".
"MIME-Version: 1.0\n".
"Content-type: text/html; charset=iso-8859-1\n".
"X-Sender: Users support$fromEmail\n".
"X-Mailer: PHP/".phpversion()."\n".
"X-Priority: 3\n".
"X-MSMail-Priority: High\n"; 
"Return-Path: <admin>$fromEmail\n";
	if( !mail( $EMail, $_SERVER['SERVER_ADDR'].'-error notification', $message, $Headers) )  $isSuccess = false;
	return $isSuccess;	
}

function sendFatalError($message,$email)
{
	require('email_accounts.php');
	$debug = 'debug trace : '.create_debug_string(debug_backtrace());
//	print_r(debug_backtrace(),true);
	return send_message_from_account($fatal_error_account, $message.' <br>'.$debug, $email,'Sarus System - '.$_SERVER['SERVER_ADDR'],'Internal fatal error');
}

function sendInfoMessage($message,$email)
{
	require('email_accounts.php');
	return send_message_from_account($info_message_account, $message, $email,'','Information letter');	
}

function sendUserMessage($message,$email,$theme='Information letter for you')
{
	require('email_accounts.php');
	return send_message_from_account($user_api_account, $message, $email,"",$theme);	
}

function send_message_from_account($account,$message,$email,$email_description,$caption_theme)
{
	$sender  = new clsEmailAlerter(false);
	return $sender->exec($account,$email,$message,$caption_theme,$email_description);
}


function send_fatal_error_default($message)
{
    $mails = ["alexus.baklanov@gmail.com","support@sarus.com"];		
//    $mails = ["alexus.baklanov@gmail.com"];		
    if( sendFatalError($message,$mails) === false) ToLog("Error sending email to ".print_r($mails,true));
}

function set_array_element_by_place( &$array, $key, $value, $element_index )
{
    if($element_index < 0 ) return;
    $ret = array();
    $index = 0;
    foreach($array as $a_key=>$a_value)
    {
	if( $index == $element_index )
	{
	    $ret[$key] = $value;
	}
	$ret[$a_key] = $a_value;
	$index++;
    }
    $array = $ret;
}


function HTML_template($file,$names,$values)
{
	$handle    = @fopen($file, "r");
	$contents  = @fread($handle, filesize($file));
	ToLog('file='.$file.' '.substr($contents,0,20));
	@fclose($handle);
	return str_replace($names,$values,$contents);
}

function insert_after($source_array,$search_key,$added_key,$added_value)
{
    $ret = [];
    foreach($source_array as $k=>$v)
    {
	$ret[$k] = $v;
	if($k == $search_key) $ret[$added_key] = $added_value;
    }
    return $ret;
}

function insert_before($source_array,$search_key,$added_key,$added_value)
{
    $ret = [];
    foreach($source_array as $k=>$v)
    {
	if($k == $search_key) $ret[$added_key] = $added_value;
	$ret[$k] = $v;
    }
    return $ret;
}



function change_key_sequence($array,$sequence,$others_fields=false,$skip_unknown=false)
{
    $res = array();
    foreach($sequence as $s)
    {
	if( isset($array[$s]) ) {
	    $res[$s] = $array[$s];
	    unset($array[$s]);
	} 
	else if($skip_unknown===false) $res[$s]=null;
    }
    if($others_fields === true) foreach($array as $key=>$v) $res[$key] = $v;
    return $res;
}

?>