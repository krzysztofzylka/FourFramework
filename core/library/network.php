<?php
return $this->network = new class(){ 
	public $version = '1.4';
	public $method = 0;
	private $methodDownloadFile = 0;
	public $curlTimeout = 1000;
	public $ignoreHttpCode = false;
	public $userAgent = null;
	public $JSONAssoc = true;
	public function __construct(){ 
		$this->userAgent = 'FourFramework ('.core::$info['version'].'/Library:'.$this->version.')';
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
				$contents = @file_get_contents($url);
				if($contents === false)
					return core::setError(1, 'error download data', '');
				return $contents;
				break;
		}
		return false;
	}
	public function getData(string $url){ 
		core::setError(); 
		switch($this->method){ 
			case 0: 
				return core::setError(2, 'no found method', 'use function _getMethod'); 
			case 1: 
				$curl = curl_init(); 
				curl_setopt_array($curl, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $url,
					CURLOPT_TIMEOUT => $this->curlTimeout,
					CURLOPT_USERAGENT => $this->userAgent
				]); 
				$getData = curl_exec($curl); 
				$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				if(($httpCode < 200 or $httpCode > 200) and $this->ignoreHttpCode === false) 
					return core::setError(3, 'error http code', 'Http Code: '.$httpCode); 
				if($getData === false) 
					return core::setError(1, 'error download data', curl_error($ch)); 
				curl_close($curl);
				if($httpCode <> 200 and $this->ignoreHttpCode === true)
					core::setError(-1, 'httpErrorCode', $httpCode);
				return $getData;
			case 2: 
				$contents = @file_get_contents($url); 
				if($contents === false)
					return core::setError(1, 'error download data', ''); 
				return $contents; 
		}
		return false; 
	}
	public function getJSONData(string $url){ 
		core::setError(); 
		$readData = $this->getData($url); 
		if(!$readData)
			return core::setError(1, 'error read data from url', ['url' => $url, 'getDataError' => core::$error]); 
		return json_decode($readData, $this->JSONAssoc); 
	}
	public function downloadFile(string $url, string $path){ 
		core::setError(); 
		if(file_exists($path)) 
			return false; 
		switch($this->method){
			case 0: 
				return core::setError(3, 'no found method', 'use function _getMethod'); 
				break;
			case 1: 
				$fp = fopen($path, 'w'); 
				$ch = curl_init($url); 
				curl_setopt_array($ch, [
					CURLOPT_FILE => $fp,
					CURLOPT_TIMEOUT => $this->curlTimeout,
					CURLOPT_USERAGENT => $this->userAgent
				]); 
				$data = curl_exec($ch);
				if(curl_errno($ch)){ 
					fclose($fp); 
					unlink($path); 
					return core::setError(1, 'error download file', curl_error($ch)); 
				}
				curl_close($ch); 
				fclose($fp); 
				return true; 
				break;
			case 2: 
				return file_put_contents($path, fopen($url, 'r')); 
				break;
		}
		return core::setError(2, 'error download file', 'unknown error'); 
	}
	public function getCurrentPageURL() : string{ 
		core::setError(); 
		$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://';
		$url .= ( $_SERVER["SERVER_PORT"] <> 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		$url .= $_SERVER["REQUEST_URI"];
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