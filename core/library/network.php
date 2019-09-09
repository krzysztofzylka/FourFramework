<?php
return $this->network = new class(){ //create library
	public $version = '1.0'; //version
	public $method = 0; //connect method
	private $methodDownloadFile = 0; //method download file
	public $curlTimeout = 1000; //curl timeout
	public function __construct(){ //main function
		$this->_getMethod(); //get method
	}
	private function _getMethod() : void{ //get method
		core::setError(); //clear error
		if(function_exists('curl_version')) //if curl
			$this->method = 1; //set method = 1
		elseif(function_exists('file_get_contents')) //if file_get_contents
			$this->method = 2; //set method = 2
	}
	public function getData(string $url){ //get data from url
		core::setError(); //clear error
		switch($this->method){ //switch method
			case 0: //no found
				return core::setError(2, 'no found method', 'use function _getMethod'); //return error 1
			case 1: //curl
				$curl = curl_init(); //init
				curl_setopt_array($curl, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $url,
					CURLOPT_TIMEOUT => $this->curlTimeout
				]); //set config
				$getData = curl_exec($curl); //get data
				$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); //get info
				if($httpCode < 200 or $httpCode > 200) //if error
					return core::setError(3, 'error http code', 'Http Code: '.$httpCode); //return error 3
				if($getData === false) //error
					return core::setError(1, 'error download data', curl_error($ch)); //return error 1
				curl_close($curl); //close curl
				return $getData; //return data
			case 2: //file_get_contents
				$contents = @file_get_contents($url); //get file
				if($contents === false) //check function
					return core::setError(1, 'error download data', ''); //return error 1
				return $contents; //return content
		}
		return false; //return false
	}
	public function getJSONData(string $url){ //get json data from url
		core::setError(); //clear error
		$readData = $this->getData($url); //get data from url
		if(!$readData)
			return core::setError(1, 'error read data from url', ['url' => $url, 'getDataError' => core::error]); //error 1
		return json_decode($readData, true); //return array
	}
	public function downloadFile(string $url, string $path){ //download file from url
		core::setError(); //clear error
		if(file_exists($path)) //if ($path) is already exists
			return false; //return false
		switch($this->method){
			case 0: //error
				return core::setError(3, 'no found method', 'use function _getMethod'); //return error 3
				break;
			case 1: //curl
				$fp = fopen($path, 'w'); //open file
				$ch = curl_init($url); //init curl
				curl_setopt_array($ch, [
					CURLOPT_FILE => $fp,
					CURLOPT_TIMEOUT => $this->curlTimeout //set timeout
				]); //set option
				$data = curl_exec($ch);
				if(curl_errno($ch)){ //if error
					fclose($fp); //clise
					unlink($path); //delete download file
					return core::setError(1, 'error download file', curl_error($ch)); //return error 1
				}
				curl_close($ch); //curl close
				fclose($fp); //file close
				return true; //return true
				break;
			case 2: //file_put_contents
				return file_put_contents($path, fopen($url, 'r')); //download file
				break;
		}
		return core::setError(2, 'error download file', 'unknown error'); //error 2
	}
	public function getCurrentPageURL() : string{ //get current URL
		core::setError(); //clear error
		$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
		$url .= ( $_SERVER["SERVER_PORT"] <> 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		$url .= $_SERVER["REQUEST_URI"];
		return $url;
	}
	public function getClientIP() : string{ //get client IP address
		core::setError(); //clear error
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			return $_SERVER['REMOTE_ADDR'];
	}
	public function ping(string $url) : int{ //get ping from domain
		core::setError(); //clear error
		$starttime = microtime(true); //start count time
		$socket = @fsockopen($domain, 80, $errno, $errstr, 10); //open socket
		$stoptime = microtime(true); //stop time
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
	public function getHeader(string $name){ //get header from name
		$headers = apache_request_headers(); //get all header
		if(isset($headers[$name])) //isset
			return null; //return null
		return $headers[$name]; //return data
	}
};
?>