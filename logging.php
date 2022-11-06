<?php

require_once('utils.php');

class Logger
{
	protected $logfilename;
	public function getLogFileName() { return $this->logfilename;}
	public function contents()
	{
		return file_get_contents($this->logfilename);
	}
	public function __construct($lfn,$isDelete = false,$isUseCurrentDir=true)
	{
		if($isUseCurrentDir === true )	$this->logfilename = GetCurrentPath('/').$lfn;
		else $this->logfilename = $lfn;
		if( $isDelete === true ) @unlink($this->logfilename);
	}
	public function EchoStr($str)
	{
		@fclose(fopen($this->logfilename,"a+t"));
		$ct = 0;        
		while(true)
		{
			$fn = fopen( $this->logfilename, "r+t");
			if( $fn == false )
			{
				usleep(100000);
				$ct++;
				if( $ct > 20 ) return false;
			}
			else break;
		}
		flock( $fn, LOCK_EX);
		fseek($fn, 0, SEEK_END);
		fprintf($fn,"%s",$str);
		fclose($fn);
		return true;	
	}
	public function L($filename, $line, $Message)
	{
		$this->FullInfoLog( $filename, $line, $Message );
	}
	public function FullInfoLog( $filename, $line, $Message )
	{
		fclose(fopen($this->logfilename,"a+t"));
		$ct = 0;        
		while(true)
		{
			$fn = fopen($this->logfilename,"r+t");
			if( $fn == false )
			{
				usleep(100000);
				$ct++;
				if( $ct > 20 ) return false;
			}
			else break;
		}
		flock( $fn, LOCK_EX);
		fseek($fn, 0, SEEK_END);
		fprintf($fn,"%s  File: %s(%d) ---> %s\n",strftime('%d.%m.%Y %H:%M:%S'), $filename, $line, $Message);
		fclose($fn);
		return true;
	}
	public function DTL( $Message )
	{
		$this->DateTimeLog($Message);
	}
	public function DateTimeLog( $Message )
	{
		@fclose(fopen($this->logfilename,"a+t"));
		$ct = 0;
		while(true)
		{
			$fn = @fopen($this->logfilename,"r+t");
			if( $fn === false )
			{
				usleep(100000);
				$ct++;
				if( $ct > 20 ) return false;
			}
			else break;
		}
		flock( $fn, LOCK_EX);
		fseek($fn, 0, SEEK_END);
		fprintf($fn,"%s %s\n",strftime('%d.%m.%Y %H:%M:%S'), $Message);
		fclose($fn);
		return true;
	}
}


$global_log_file_name = "sql_errors.log";
$global_log_off = false;
function ToLog( $Message,$isLogStack=false)
{	
	global $global_log_off,$global_log_file_name;
	if($global_log_off === true) return;
	$log = new Logger($global_log_file_name);
	$arrs = debug_backtrace();	
	preg_match('{([^\\\/]+)$}',$arrs[0]['file'],$pok);
	$log->FullInfoLog($pok[1], $arrs[0]['line'], "MESSAGE: ".$Message);
	if( $isLogStack == true )
	{
		$to = count($arrs);
		$stack = array();
		for($i=$to-1;$i>=0;$i--) $stack[]=$arrs[$i]['file'].'('.$arrs[$i]['line'].')';
		$log->DTL("Calling sequence: ".implode(' -> ',$stack));
	}	
}

function _ToLog( $Message,$isLogStack=false)
{	
	$log = new Logger("requests_answers.log");
	$arrs = debug_backtrace();	
	preg_match('{([^\\\/]+)$}',$arrs[0]['file'],$pok);
	$log->FullInfoLog($pok[1], $arrs[0]['line'], "MESSAGE: ".$Message);
	if( $isLogStack == true )
	{
		$to = count($arrs);
		$stack = array();
		for($i=$to-1;$i>=0;$i--) $stack[]=$arrs[$i]['file'].'('.$arrs[$i]['line'].')';
		$log->DTL("Calling sequence: ".implode(' -> ',$stack));
	}	
}





?>