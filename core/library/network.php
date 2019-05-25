<?php
return $this->network = new class($this->core){
	protected $core;
	public $method = 0;
	private $methodDownloadFile = 0;
	public $curlTimeout = 1000;
	public $version = '1.0';
	public function __construct($obj){
		$this->core = $obj;
		$this->_getMethod();
	}
	private function _getMethod() : void{
		$this->core->returnError();
		if(function_exists('curl_version'))
			$this->method = 1;
		elseif(function_exists('file_get_contents'))
			$this->method = 2;
	}
	public function getData(string $url){
		$this->core->returnError();
		switch($this->method){
			case 0:
				return $this->core->returnError(2, 'no found method', 'use function _getMethod');
				break;
			case 1:
				$curl = curl_init();
				curl_setopt_array($curl, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $url,
					CURLOPT_TIMEOUT => $this->curlTimeout
				]);
				$getData = curl_exec($curl);
				if($getData === false)
					return $this->core->returnError(1, 'error download data', curl_error($ch), 'Error download data, error: '.curl_error($ch), 'library network', 'error');
				curl_close($curl);
				return $getData;
			case 2:
				$contents = @file_get_contents($url);
				if($contents === false)
					return $this->core->returnError(1, 'error download data', '', 'Error download data', 'library network', 'error');
				else
					return $contents;
		}
		return false;
	}
	public function getJSONData(string $url){
		$this->core->returnError();
		$readData = $this->getData($url);
		if(!$readData)
			return $this->core->returnError(1, 'error read data from url', ['url' => $url]); //error 1
		return json_decode($readData, true);
	}
	public function downloadFile(string $url, string $path){
		$this->core->returnError();
		if(file_exists($path))
			return false;
		switch($this->method){
			case 0:
				return $this->core->returnError(3, 'no found method', 'use function _getMethod');
				break;
			case 1:
				$fp = fopen($path, 'w');
				$ch = curl_init($url);
				curl_setopt_array($ch, [
					CURLOPT_FILE => $fp,
					CURLOPT_TIMEOUT => $this->curlTimeout
				]);
				$data = curl_exec($ch);
				if(curl_errno($ch)){
					fclose($fp);
					unlink($path);
					return $this->core->returnError(1, 'error download file', curl_error($ch), 'Error download file, error: '.curl_error($ch), 'library network', 'error');
				}
				curl_close($ch);
				fclose($fp);
				return true;
				break;
			case 2:
				return file_put_contents($path, fopen($url, 'r'));
				break;
		}
		return $this->core->returnError(2, 'error download file', 'unknown error'); //error 2
	}
	public function getCurrentPageURL() : string{
		$this->core->returnError();
		$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
		$url .= ( $_SERVER["SERVER_PORT"] <> 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		$url .= $_SERVER["REQUEST_URI"];
		return $url;
	}
	public function getClientIP() : string{
		$this->core->returnError();
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			return $_SERVER['REMOTE_ADDR'];
	}
	public function ping($domain) : int{
		$starttime = microtime(true);
		$file = @fsockopen($domain, 80, $errno, $errstr, 10);
		$stoptime = microtime(true);
		$status = 0;
		if (!$file)
			$status = -1;
		else {
			fclose($file);
			$status = ($stoptime - $starttime) * 1000;
			$status = floor($status);
		}
		return $status;
	}
	public function __debugInfo(){
		return [
			'version' => $this->version,
			'method' => $this->method,
			'curlTimeout' => $this->curlTimeout,
		];
	}
};
?>