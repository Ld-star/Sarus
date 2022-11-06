<?php
class DateTimeClass
{
	protected $timestamp='';
	protected $default_zone;
	protected $variants = array(
		"GMT"=>array('offset_string'=>"+0000",'offset'=>0),
		"EST"=>array('offset_string'=>"+0000",'offset'=>0),
		"SERVER TIME"=>array('offset_string'=>"+0000",'offset'=>0),
		);
	public function getTimeDetails()
	{
		$d = $this->variants[$this->default_zone];
		return array('datetime_zone'=>$this->default_zone,'datetime_offset'=>$d['offset'],'datetime_offset_string'=>$d['offset_string']);
	}
	public function getDefaultZone() { return $this->default_zone; }
	public function getDefaultTime($timestamp='')
	{
		if($timestamp == '' ) $timestamp = time();
		$gmt = $this->getGMTTime($timestamp);
		$data = $this->variants[$this->default_zone];
		return $gmt + $data['offset'];
	}
	public function getDefaultTimeString($timestamp='',$date_format='d.m.Y H:i:s')
	{
		if( $timestamp == '' ) $timestamp = time();
		$gmt = $this->getGMTTime($timestamp);
		$data = $this->variants[$this->default_zone];
		return $this->default_zone.' '.date($date_format, $gmt + $data['offset'] ).' UTC'.$data['offset_string'];
	}
	public function __construct($timestamp='',$default_zone="GMT")
	{
		if($timestamp == '') $this->timestamp = time();
		else $this->timestamp = $timestamp;
		$this->default_zone = $default_zone;
		$this->define_offset();
	}
	protected function define_offset()
	{
		switch($this->default_zone)
		{
			case "GMT": break;
			case "EST":
//				if( $this->isDayLightSaving($GMTDateTime,$this->timestamp) == true ) $hours_shift = 4;
//				else $hours_shift = 5;
//		for EST time we have constant offset whole year = GMT -05:00
				$hours_shift = 5;
				$this->variants['EST']['offset_string']="-0{$hours_shift}00";
				$this->variants['EST']['offset']=-$hours_shift*3600;
			break;
			case "SERVER TIME":
			default:
				$this->defaul_zone="SERVER TIME";
				$offset = date("Z");
				$this->variants['SERVER TIME']['offset']=$offset;
				if($offset < 0 ) {
					$sign="-";
					$offset = -$offset;
				}
				else $sign="+";
				$hours = floor( $offset / 3600 );
				if($hours < 10) $hours = '0'.$hours;				
				$minutes = floor(($offset % 3600 ) / 60);
				if($minutes < 10) $minutes = '0'.$minutes;
				$this->variants['SERVER TIME']['offset_string']="$sign{$hours}{$minutes}";
			break;
		}
	}
	
	protected function getGMTTime($timestamp='')
	{
		$dt = $this->timestamp;
		if( $timestamp != '' ) $dt = $timestamp;
		$offset = date("Z", $dt);
		return $dt - $offset;
	}
	protected function isDayLightSaving(&$GMTDateTime, $timestamp = '')
	{
		$dt = $this->getGMTTime($timestamp);
		$GMTDateTime = $dt; 
		//echo strftime("%d.%m.%Y %H:%M:%S",$GMTDateTime).'        ';
		$dtt = getdate($dt);
		$ldt = mktime(0,0,0,3,1,$dtt['year']);
		$ldt_from = strtotime('second sunday 07:00:00', $ldt); // + 5 hours
		$ldt = mktime(0,0,0,11,1,$dtt['year']);
		$ldt_to = strtotime('first sunday 06:00:00', $ldt);
	//	echo strftime("%d.%m.%Y %H:%M:%S",$ldt_from).' - '.strftime("%d.%m.%Y %H:%M:%S",$ldt_to);
		if( $dt > $ldt_from && $dt < $ldt_to) return true;
		return false;
	}
};
?>