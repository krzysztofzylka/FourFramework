<?php
return $this->network = new class(){ 
	public $version = '1.8';
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
		$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']; //<http/https>://{host}:{port}
		if($option['request_uri'] === false)
			return $url.'/'; //request_uri
		$url .= $_SERVER['REQUEST_URI']; //<dir>/<dir>/<...>/<file>
		if($option['dirOnly'])
			return $url = str_replace(basename($url), '', $url); //dirOnly
		return $url;
	}
	public function getClientIP() : string{
		core::setError();
		if(!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
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
	public function getBrowserInfo() : array{
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		//platforma
		$platform = 'Unknown';
		if(preg_match('/linux/i', $userAgent)) $platform = 'Linux';
		elseif(preg_match('/macintosh|mac os x/i', $userAgent)) $platform = 'Mac';
		elseif (preg_match('/windows|win32/i', $userAgent)) $platform = 'Windows';
		//przeglądarka
		$browser = 'Unknown';
		if(preg_match('/MSIE/i',$userAgent) && !preg_match('/Opera/i',$userAgent)) $browser = 'Internet Explorer';
		elseif(preg_match('/Firefox/i',$userAgent)) $browser = 'Mozilla Firefox';
		elseif(preg_match('/Vivaldi/i',$userAgent)) $browser = 'Vivaldi';
		elseif(preg_match('/OPR/i',$userAgent)) $browser = 'Opera';
		elseif(preg_match('/Opera/i',$userAgent)) $browser = 'Opera';
		elseif(preg_match('/Chromium/i',$userAgent) && !preg_match('/Edge/i',$userAgent)) $browser = 'Chromium';
		elseif(preg_match('/Chrome/i',$userAgent) && !preg_match('/Edge/i',$userAgent)) $browser = 'Google Chrome';
		elseif(preg_match('/Safari/i',$userAgent) && !preg_match('/Edge/i',$userAgent)) $browser = 'Safari';
		elseif(preg_match('/Netscape/i',$userAgent)) $browser = 'Netscape';
		elseif(preg_match('/Edge/i',$userAgent)) $browser = 'Edge';
		elseif(preg_match('/Trident/i',$userAgent)) $browser = 'Internet Explorer';
		//wersja przeglądarki
		$version = 'Unknown';
		switch($browser){
			case 'Internet Explorer':
				preg_match('/(MSIE)+ ([0-9.]+)/m', $userAgent, $matches);
				if(count($matches) === 3)
					$version = $matches[2];
				else{
					preg_match('/(rv:)+([0-9.]+)/m', $userAgent, $matches);
					if(count($matches) === 3)
						$version = $matches[2];
				}
				break;
			case 'Safari':
				preg_match('/(Version)+\/([0-9.]+)/m', $userAgent, $matches);
				if(count($matches) === 3)
					$version = $matches[2];
				break;
			case 'Chromium':
			case 'Google Chrome':
				preg_match('/(Chrome)+\/([0-9.]+)/m', $userAgent, $matches);
				if(count($matches) === 3)
					$version = $matches[2];
				break;
			case 'Mozilla Firefox':
				preg_match('/(Firefox)+\/([0-9.]+)/m', $userAgent, $matches);
				if(count($matches) === 3)
					$version = $matches[2];
				break;
			case 'Vivaldi':
				preg_match('/(Vivaldi)+\/([0-9.]+)/m', $userAgent, $matches);
				if(count($matches) === 3)
					$version = $matches[2];
				break;
			case 'Opera':
				preg_match('/(Version)+\/([0-9.]+)/m', $userAgent, $matches);
				if(count($matches) === 3)
					$version = $matches[2];
				else{
					preg_match('/(OPR)+\/([0-9.]+)/m', $userAgent, $matches);
					if(count($matches) === 3)
						$version = $matches[2];
					else{
						preg_match('/(Opera)+\/([0-9.]+)/m', $userAgent, $matches);
						if(count($matches) === 3)
							$version = $matches[2];
					}
				}
				break;
			case 'Edge':
				preg_match('/(Edge)+\/([0-9.]+)/m', $userAgent, $matches);
				if(count($matches) === 3)
					$version = $matches[2];
				break;
		}
		//zwrócone dane przez funkcje
		return [
			'userAgent' => $userAgent,
			'browser' => $browser,
			'version' => $version,
			'platform' => $platform
		];
	}
};
?>