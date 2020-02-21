<?php
return $this->network = new class(){ 
	public $version = '1.6';
	public $method = 0;
	public function __construct(){ 
		$this->_getMethod(); 
	}
	private function _getMethod() : void{ 
		core::setError(); 
		if(function_exists('curl_version')) 
			$this->method = 1; 
		elseif(function_exists('file_get_contents')) 
			$this->method = 2; 
	}
	public function get(string $url, array $option = []){
		core::setError();
		if(!isset($option['userAgent']))
			$option['userAgent'] = 'FourFramework ('.core::$info['version'].'/Library:'.$this->version.')';
		if(!isset($option['timeout']))
			$option['timeout'] = 1000;
		if(!isset($option['ignoreHttpCode']))
			$option['ignoreHttpCode'] = false;
		if(!isset($option['JSONData']))
			$option['JSONData'] = false;
		if(!isset($option['JSONAssoc']))
			$option['JSONAssoc'] = true;
		if(!isset($option['saveToFile']))
			$option['saveToFile'] = false;
		switch($this->method){
			case 1: //curl
				$curl = curl_init();
				$opt = [ //opt
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $url,
					CURLOPT_TIMEOUT => $option['timeout'],
					CURLOPT_USERAGENT => $option['userAgent']
				];
				if($option['saveToFile'] <> false){ //download
					$fp = fopen($option['saveToFile'], 'w'); 
					$opt[CURLOPT_FILE] = $fp; //set opt
				}
				curl_setopt_array($curl, $opt); //set array opt
				$getData = curl_exec($curl);
				$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				if($httpCode < 200 and $httpcode > 200) //httpcode
					if($option['ignoreHttpCode'] === false)
						return core::setError(2, 'error http code', 'Http Code: '.$httpCode);
				if(curl_errno($curl)){
					if($option['saveToFile'] <> false){ //download
						fclose($fp);
						unlink($option['saveToFile']);
					}
					return core::setError(3, 'error download data', curl_error($curl));
				}
				if($option['saveToFile'] <> false){ //download
					fclose($fp);
					return true;
				}elseif($option['JSONData'] === true) //json
					return json_decode($getData, $option['JSONAssoc']);
				else //other
					return $getData;
				break;
			default: //other
				if($option['saveToFile'] <> false){
					file_put_contents($option['saveToFile'], fopen($url, 'r'));
					return true;
				}
				$contents = @file_get_contents($url);
				if($contents === false)
					return core::setError(1, 'error download data', '');
				return $contents;
				break;
		}
		return false;
	}
	public function getCurrentPageURL(array $option = []) : string{ 
		core::setError();
		if(!isset($option['request_uri']))
			$option['request_uri'] = true;
		if(!isset($option['dirOnly']))
			$option['dirOnly'] = false;
		$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
		$url .= ( $_SERVER["SERVER_PORT"] <> 80 and $_SERVER["SERVER_PORT"] <> 443  ) ? ":".$_SERVER["SERVER_PORT"] : "";
		if($option['request_uri'])
			$url .= $_SERVER["REQUEST_URI"];
		if($option['dirOnly'])
			$url = str_replace(basename($_SERVER['PHP_SELF']).($_SERVER['QUERY_STRING'] === ''?'':'?'.$_SERVER['QUERY_STRING']), '', $url);
		return $url;
	}
	public function getClientIP() : string{
		core::setError();
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			return $_SERVER['REMOTE_ADDR'];
	}
	public function ping(string $url) : int{ 
		core::setError(); 
		$starttime = microtime(true); 
		$socket = @fsockopen($url, 80, $errno, $errstr, 10); 
		$stoptime = microtime(true); 
		$status = 0;
		if(!$socket)
			$status = -1;
		else {
			fclose($socket);
			$status = ($stoptime - $starttime) * 1000;
			$status = floor($status);
		}
		return $status;
	}
	public function getHeader(string $name){
		core::setError();
		$headers = apache_request_headers(); 
		if(isset($headers[$name])) 
			return null; 
		return $headers[$name]; 
	}
};
?>