<?php
return $this->network = new class($this->core){
	protected $core;
	private $method = 0;
	private $methodDownloadFile = 0;
	private $curlTimeout = 1000;
	public function __construct($obj){
		$this->core = $obj;
		$this->_getMethod();
	}
	//get method
	private function _getMethod() : void{
		$this->core->returnError();
		//curl
		if(function_exists('curl_version'))
			$this->method = 1;
		//file_get_contents
		elseif(function_exists('file_get_contents'))
			$this->method = 2;
		else
			return $this->core->returnError(1, 'error set method'); //error 1
		return;
	}
	//download data from url
	public function getData(string $url){
		$this->core->returnError();
		switch($this->method){
			case 0: //if error
				return false;
				break;
			//curl
			case 1:
				$curl = curl_init();
				curl_setopt_array($curl, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $url,
					CURLOPT_TIMEOUT => $this->curlTimeout
				]);
				$getData = curl_exec($curl);
				if($getData === false)
					return $this->core->returnError(1, 'error download data', curl_error($ch), 'Error download data, error: '.curl_error($ch), 'library network', 'error'); //error 1
				curl_close($curl);
				return $getData;
			//file_get_contents
			case 2:
				return file_get_contents($url);
		}
		return false;
	}
	//download JSON from url
	public function getJSONData(string $url){
		$this->core->returnError();
		$readData = $this->getData($url);
		if(!$readData)
			return $this->core->returnError(1, 'error read data from url', ['url' => $url]); //error 1
		return json_decode($readData, true);
	}
	//download file from url
	public function downloadFile(string $url, string $path){
		$this->core->returnError();
		if(file_exists($path))
			return false;
		switch($this->method){
			case 0: //if error
				return false;
				break;
			//curl
			case 1:
				$fp = fopen($path, 'w');
				$ch = curl_init($url);
				curl_setopt_array($ch, [
					CURLOPT_FILE => $fp,
					CURLOPT_TIMEOUT => $this->curlTimeout
				]);
				$data = curl_exec($ch);
				curl_close($ch);
				if(curl_errno($ch)){
					fclose($fp);
					unlink($path);
					return $this->core->returnError(1, 'error download file', curl_error($ch), 'Error download file, error: '.curl_error($ch), 'library network', 'error'); //error 1
				}
				fclose($fp);
				return true;
				break;
			//file_put_contents
			case 2:
				return file_put_contents($path, fopen($url, 'r'));
				break;
		}
		//jeżeli błąd
		return $this->core->returnError(2, 'error download file', 'unknown error'); //error 2
	}
	//return current page url
	public function getCurrentPageURL() : string{
		$this->core->returnError();
		$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
		$url .= ( $_SERVER["SERVER_PORT"] <> 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		$url .= $_SERVER["REQUEST_URI"];
		return $url;
	}
	//return client IP
	public function getClientIP() : string{
		$this->core->returnError();
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return  $_SERVER['HTTP_X_FORWARDED_FOR'];
		else return $_SERVER['REMOTE_ADDR'];
	}
};
?>