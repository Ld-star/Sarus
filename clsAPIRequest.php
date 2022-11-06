<?php
class Internal_Exception extends Exception
{
	protected $isUseHeaders;
	public $body='';
	public function __construct($isUseHeaders=false,$body='')
	{
		$this->isUseHeaders = $isUseHeaders;
		$this->body=$body;
		parent::__construct('Internal_Exception');
	}
	public function isUseHeaders() { return $this->isUseHeaders; } 
};

class clsAPIRequest
{
	protected $EOL="\r\n";
	protected $useSSL = 0;
	protected $server_address,$port;
	protected $httpVersion;
	protected $query='';
	protected $chunk_size = 1024*500;
//	protected $buffer_size = 1024*1024*2;
	public function get_request($replace=['api_token'=>'xxxxxxxxxx'])
	{

/*		if($replace !== false)
		{
			parse_str($this->query,$res);
			$ready="";
			foreach($res as $k=>$v)
			{
				if($ready != "") $ready .= "&";
				if( isset($replace[$k]) ) $ready .= $k.'='.$replace[$k];
				else $ready .= $k.'='.$v;
			}			
			return $ready;
		}
		return $this->query; 
*/
		if($replace !== false)
		{
			$pos = strpos($this->query,"?");
			if($pos === false) return "? is not found in ".$this->query;
			$before = substr($this->query,0,$pos+1);
			$query = substr($this->query,$pos+1);
			parse_str($query,$res);
			$ready="";
			foreach($res as $k=>$v)
			{
				if($ready != "") $ready .= "&";
				if( isset($replace[$k]) ) $ready .= $k.'='.$replace[$k];
				else $ready .= $k.'='.$v;
			}			
			return $before.$ready;
		}
		return $this->query; 		
	}
	public function __construct( $sa, $port=80, $useSSL = 0, $httpVersion="1.1", $num_attempts=3 )
	{
		$this->httpVersion = $httpVersion;
		$this->num_attempts = $num_attempts;
		$this->server_address = $sa;
		$this->port = $port;
		$this->useSSL = $useSSL;
		ToLog('clsAPIRequest::construct: '.$sa.' '.$port.' '.$useSSL);
	}


	public function upload_file_variant($uri,$file_name,$file_argument,$args,$headers = array())
	{
		$pi = pathinfo($file_name);
		$b = "TrEaSuReErUsAeRt";
		$boundary = "--".$b;
//		$boundary = $b;
		$template = "Content-Disposition: form-data; name=";

		$data = '';

		foreach($args as $key=>$value)
		{
		    if($data != '') $data.= "&";
		    $data .= "$key=$value";
		}
		if($data != '') 
		{
		    $uri .= "?$data";
		    $data = '';
		}
		$h = '';
		foreach($headers as $k=>$v) $h .= "$k: $v".$this->EOL;

		$data .= $boundary.$this->EOL.$template.'"'.$file_argument.'"; filename="'.$pi['basename'].'"'.$this->EOL.
			"Content-Type: application/octet-stream".$this->EOL.$this->EOL.file_get_contents($file_name).$this->EOL;
		$data .= $boundary."--".$this->EOL.$this->EOL;
		
		$request_data = 
			"POST $uri HTTP/{$this->httpVersion}{$this->EOL}".
			"Host: {$this->server_address}{$this->EOL}".
			"Connection: Keep-Alive{$this->EOL}".
			"Cache-Control: no-cache{$this->EOL}".
			"Content-Type: multipart/form-data; boundary=$b{$this->EOL}".
			$h.
			"Content-Length: ".strlen($data).$this->EOL.$this->EOL.$data;
		ToLog($request_data);
		$res = $this->send_request($request_data);
		return $res;
	}


	
	public function upload_file($uri,$file_name,$file_argument,$args,$headers = array())
	{
		$pi = pathinfo($file_name);
		$b = "TrEaSuReErUsAeRt";
		$boundary = "--".$b;
//		$boundary = $b;
		$template = "Content-Disposition: form-data; name=";

		$data = '';

		foreach($args as $key=>$value)
		{
			$data .= $boundary.$this->EOL.$template.'"'.$key.'"'.$this->EOL.$this->EOL.$value.$this->EOL;
		}
		$h = '';
		foreach($headers as $k=>$v) $h .= "$k: $v".$this->EOL;

		$data .= $boundary.$this->EOL.$template.'"'.$file_argument.'"; filename="'.$pi['basename'].'"'.$this->EOL.
			"Content-Type: application/octet-stream".$this->EOL.$this->EOL.file_get_contents($file_name).$this->EOL;
		$data .= $boundary."--".$this->EOL.$this->EOL;
		
		$request_data = 
			"POST $uri HTTP/{$this->httpVersion}{$this->EOL}".
			"Host: {$this->server_address}{$this->EOL}".
			"Connection: Keep-Alive{$this->EOL}".
			"Cache-Control: no-cache{$this->EOL}".
			"Content-Type: multipart/form-data; boundary=$b{$this->EOL}".
			$h.
			"Content-Length: ".strlen($data).$this->EOL.$this->EOL.$data;
		ToLog($request_data);
		$res = $this->send_request($request_data);
		return $res;
	}
	
	public function request($api_request,$args,$method,$headers=array(),$isDefaultRequest=false)
	{
		try
		{
			$hs = '';
			foreach($headers as $key=>$value)
			{
			    if($hs != '') $hs.=',';
			    $hs .= $key.': '.$value.$this->EOL;
			}
													
			switch($method)
			{
				case 'PUT':
				case 'put':
				case 'POST':
				case 'post':
					$query = $this->make_post_query($args,$api_request,$method,$hs,$isDefaultRequest);
					ToLog('!!!! post query = '.$query);
				break;
				case 'GET':
				case 'get':
					$query = $this->make_get_query($api_request,$args,$hs,$isDefaultRequest);
					ToLog('!!!! get query = '.$query);
				break;
				default:
					ToLog("$method is not supported for now");
					return false;
				break;
			}
			$res = $this->send_request($query);
			return $res;
		}
		catch(APIRequest_Exception $ex)
		{
			$contents = ob_get_contents();
			ob_end_clean();
			ToLog('APIRequest_Exception on '.$ex->getFile.'('.$ex->getLine().') Details '.$contents);
		}
		return false;
	}
	protected function make_get_query($api,$args,$headers='',$isDefaultRequest=false)
	{	
		$ready = array();
		if( $isDefaultRequest === false ) foreach($args as $key=>$value) $ready[]=$key.'='.rawurlencode($value);
		else foreach($args as $key=>$value) $ready[]=$key.'/'.rawurlencode($value);
		if( count($args) == 0 )$s='';
		else 
		{
			if( $isDefaultRequest === false ) $s = '?'.implode('&',$ready);
			else $s = '/'.implode('/',$ready);
		}
		$this->query = "GET $api$s";
		return "GET $api$s HTTP/{$this->httpVersion}{$this->EOL}".$headers.
		"Host: {$this->server_address}{$this->EOL}{$this->EOL}";
	}
	protected function make_post_query($args,$get,$method,$headers='',$isDefaultRequest=false)
	{
		if($isDefaultRequest === false)	
		{
		    $contents = json_encode($args);
		    $ct = 'application/json';
		}
		else
		{
		    $ready = array();
		    foreach($args as $key=>$value) $ready[]=$key.'='.rawurlencode($value);
		    $contents = implode('&',$ready);
		    $ct = "application/x-www-form-urlencoded";
		}
		$len_contents = strlen($contents);
		$ret = 
		"$method $get HTTP/{$this->httpVersion}{$this->EOL}".
		"Host: {$this->server_address}{$this->EOL}".$headers.
		"Content-Type: $ct{$this->EOL}".
		"Content-Length: ".$len_contents.$this->EOL.$this->EOL.$contents;	
		$this->query = $ret;
		return $ret;
	}
	protected function send_request($data)
	{
		try 
		{
			ob_start();
			$attempts = 0;
			$mt = new Timer;
			$mt->set('1');	
			if( $this->useSSL == 1 ) $ssl = "ssl://";
			else $ssl = '';
			while(1)
			{
				$file = fsockopen($ssl.$this->server_address, $this->port, $errno, $errstr, 30);
				ToLog(($attempts+1).' attempt: opening '.$this->server_address.':'.$this->port.' takes '.$mt->get('1'));$mt->set('1');
				if( $file === false ) 
				{ 
					ToLog("Error opening ".$this->server_address." error code is [".$errno,"] Description: [$errstr]");
					$attempts++;
					if($attempts < $this->num_attempts  )
					{
						sleep(1);
						continue;
					}
					throw new Internal_Exception;
				}
				break;
			}
//			if( stream_set_read_buffer($file,$this->buffer_size) != 0 ) throw new Internal_Exception;
			if( stream_set_chunk_size($file,$this->chunk_size) === false ) 
			{
			    ToLog('Error in stream_set_chunk_size.');
			    throw new Internal_Exception;
			}
			if( fputs($file, $data)==FALSE) throw new Internal_Exception;
			ToLog('send data '.$this->server_address.' = '.$mt->get('1'));$mt->set('1');
			$headers = array();
			$server_answer_code = '';
			$server_answer_description = '';
			$file_size = 0;	
		// sent headers				
			while( ($res = fgets($file,256000))!="\r\n" && !feof($file) )
			{
				$res = strtolower($res);
				$pos = strpos($res,":");
				if( $pos === false )
				{
					if( preg_match('{http/\\d\\.\\d\\s(\\d+)\\s(?:\r\n|(.+)\r\n)}',$res,$pockets) )
					{
						$server_answer_code = $pockets[1];
						$server_answer_description = isset($pockets[2]) ? $pockets[2]:'';
						continue;
					} else throw new Internal_Exception(false,'Error reading HTTP header: '.$res);
				}
				$key = substr($res,0,$pos);
				$val = substr($res,$pos+2,-2);
				$headers[$key] = $val;
				
				if( preg_match('{content-length:\\s(\\d+)}', $res, $pockets) )
				{
					$file_size = $pockets[1];
					settype($file_size, "integer");	
					$isChunked = false;
				}
				if( strpos($res,'transfer-encoding: chunked') !== FALSE ) $isChunked = true;
			}
			ToLog('getting headers '.$this->server_address.' = '.$mt->get('1'));$mt->set('1');			
			ToLog('headers are: '.print_r($headers,true));
			if($server_answer_code != '' )
			{
				$isGzippedData =isset($headers['content-encoding']);
			//	$isGzippedData = in_array('Content-Encoding: gzip'.$this->EOL,$headers);
				if( $isChunked === true ) $echo  = $this->getChunkedData($file);
				else $echo = $this->getContentLengthData($file_size,$file);
			}
			else 
			{
				echo 'No response code from server<br>';
				throw new Internal_Exception(true);
			}
			ToLog('got result '.$this->server_address.' = '.$mt->get('1'));$mt->set('1');
			if( $isGzippedData === true ) $echo = $this->uncompress_data($echo);
			ob_end_clean();
			return array('answer_code'=>$server_answer_code,'answer_description'=>$server_answer_description, 'headers'=>$headers,'body'=>$echo);
		}
		catch(Internal_Exception $ex)
		{
			ToLog('Exception at '.$ex->getFile().'('.$ex->getLine().') debug string is '.$ex->getTraceAsString());//create_debug_string(debug_backtrace()));			
			ToLog('UNCOMPRESSED DATA IS '.$ex->body);
			$st = ob_get_contents();
			ob_end_clean();
			
			if($ex->isUseHeaders() === true ) return array('answer_code'=>false,'answer_description'=>$st,'headers'=>$headers,'body'=>$ex->body);
			else return array('answer_code'=>false,'answer_description'=>$st,'body'=>$ex->body);
		}
	}
	protected function uncompress_data($echo,$isThrowException=true)
	{	
		ToLog('COMPRESSED DATA: '.substr($echo,0,60).'...');
		$e = @gzuncompress($echo);
		if($e === FALSE )
		{
//			$echo = gzinflate($echo); 
			$echo = @gzdecode($echo); 
			if( $echo !== FALSE ) ToLog('UNCOMPRESSED DATA: '.substr($echo,0,60).'...');
			else 
			{
				ToLog('Error uncompressing compressed data: '.$echo);
				if($isThrowException===true) throw new Internal_Exception(true);
			}
		} 
		else { $echo = $e; ToLog('UNCOMPRESSED DATA: '.substr($echo,0,60).'...'); }
		return $echo;
	}
	protected function read_chunk_length($file, $timeout_reading, &$return)
	{		
		$no_data_second = 0;
		$buffer = '';
		$len=10;
		while($len > 0 )
		{
			$read_bytes = fgets($file,$len);
			$l = strlen($read_bytes);
			if( $l > 0 ) 
			{
				$no_data_second = 0;
				$pos = strrpos($read_bytes,"\r\n");
				if($pos !== false ) 
				{
					$read_bytes = substr($read_bytes,0,$pos);
					$return = $buffer.$read_bytes;
					return true;
				}
				$len -= $l;
				$buffer .= $read_bytes;
			}
			else
			{
				usleep(10000);
				$no_data_second++;
				if( $no_data_second > $timeout_reading) break;
			}
		}
		$return = $buffer;
		return false;
	}
	protected function read_str($file, $len, $timeout_reading, &$return)
	{
		$no_data_second = 0;
		$buffer = '';		
		while($len > -2 )
		{
			$read_bytes = fread($file,$len+2);
			$l = strlen($read_bytes);
			if( $l > 0 ) 
			{
				$no_data_second = 0;
				ToLog('reading block ('.$l.') bytes ');
				if( $l == $len + 2 )
				{
					$return = $buffer.$read_bytes;
					$pos = strrpos($return,"\r\n");
					if($pos !== false )
					{
						$return = substr($return, 0, $pos);
						return true;
					}
					else {
						throw new Internal_Exception();
					}
				}
				$len -= $l;
				$buffer .= $read_bytes;
			}
			else
			{
				ToLog("empty reading $no_data_second ? $timeout_reading");
				usleep(10000);
				$no_data_second++;
				if( $no_data_second > $timeout_reading) break;
			}
		}
		$return = $buffer;
		return false;
	}
	protected function getChunkedData($file)
	{
		$timeout_reading = 6000;
		$isConnectionTimeout = true;
//		ToLog('Here 1');
		$echo="";
		if( stream_set_blocking ( $file , 0 ) == false )
		{
			ToLog( 'Switching to nonblocking mode is false trying read from blocking mode ');
//			ToLog('Here 2');
			while( !feof($file ) )
			{
				$read_bytes = fgets($file,10);
				if( $read_bytes === false ) throw new Internal_Exception(true);
				$pos = strrpos($read_bytes,"\r\n");
				if($pos !== false ) $read_bytes = substr($read_bytes,0,$pos);

				if( $read_bytes == "0" )
				{
					$isConnectionTimeout = false;
					//fgets($file,10);
					break;
				}
				$len_chunck = hexdec($read_bytes);
				$read_bytes = fread($file,$len_chunck+2);
				if( $read_bytes === false ) throw new Internal_Exception(true);
				$l = strlen($read_bytes);
				if( $l > 0 ) $echo .= $read_bytes;
			}								
		}
		else
		{
			$count_bytes=0;
			$no_data_second = 0;
			$total_len = 0;
//			ToLog('Here 3');
			while(1)
			{
				if( $this->read_chunk_length($file,$timeout_reading,$read_bytes) === false) 
				{
					ToLog('read_chunk_length return false');
					break;
				}
				ToLog('block = ['.$read_bytes.']');
				if( $read_bytes == "0" )
				{					
					$isConnectionTimeout = false;
					break;
				}				
				$len_chunk = hexdec($read_bytes);
				ToLog('len_chunk = '.$len_chunk);
				
				$total_len += $len_chunk;
				$is_success = $this->read_str( $file, $len_chunk, $timeout_reading, $read_bytes);
				$echo .= $read_bytes;
				if( $is_success === false ) throw new Internal_Exception( true, $echo ); 
			}
			ToLog('total_len = '.$total_len);
		}					
		if( $isConnectionTimeout === true )
		{
			ToLog('Connection with server was lost');
			throw new Internal_Exception(true,$echo);
		}
		return $echo;
	}
	protected function getContentLengthData($file_size,$file)
	{
		$echo="";
		$block_size = 512000;
		ToLog('file_size is '.$file_size);
		if( $file_size > 0 ) 
		{
			$timeout_reading = 10;
			if( stream_set_blocking ( $file , 0 ) == false )
			{
				ToLog('Switching to nonblocking mode is false trying read from blocking mode ');
				while(!feof($file ) )
				{
					ToLog('pass here 1');
					$read_bytes = fread( $file, $block_size );
					$l = strlen($read_bytes);
					if( $l > 0 ) 
					{
						$echo .= $read_bytes;
						$count_bytes += $l;
					}
				}								
			}
			else
			{
				ToLog('pass here 2');
				$count_bytes=0;
				$no_data_second = 0;
				while($file_size != $count_bytes)
				{
					ToLog('pass here 3');
					$read_bytes = fread( $file, 512 );
					$l = strlen($read_bytes);
					if( $l > 0 ) 
					{
						$echo .= $read_bytes;
						$count_bytes += $l;
						$no_data_second = 0;
					}
					else {
					ToLog('pass here 4 goto sleep');		
						sleep(1);
						$no_data_second++;
						if( $no_data_second > $timeout_reading) break;
					}
					ToLog('file size = '.$file_size.' count_bytes='.$count_bytes);
				}
			}					
			if( $file_size != $count_bytes )
			{
				ToLog('Connection with server was lost. '.$count_bytes.' of '.$file_size.' was copied');
				throw new Internal_Exception(true);
			}
		}
		return $echo;
	}	
};
?>