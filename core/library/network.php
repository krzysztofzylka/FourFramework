<?php
return $this->network = new class(){ 
	public $version = '1.10';
	public $method = 0;

	public function __construct(){
		core::setError();

		$this->_getMethod();
	}
	private function _getMethod() : void{ 
		core::setError();

		if (function_exists('curl_version')) {
			$this->method = 1;
		} elseif (function_exists('file_get_contents')) {
			$this->method = 2;
		}
	}
	public function get(string $url, array $option = []) {
		core::setError();

		$option = $this->_getDefaultOptionForGet($option);
		
		switch($this->method){
			case 1:
				$curl = curl_init();
				$opt = [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $url,
					CURLOPT_TIMEOUT => $option['timeout'],
					CURLOPT_USERAGENT => $option['userAgent']
				];

				if ($option['saveToFile']) {
					$fp = fopen($option['saveToFile'], 'w');
					$opt[CURLOPT_FILE] = $fp;
				}

				curl_setopt_array($curl, $opt);
				$getData = curl_exec($curl);
				$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

				if ($httpCode < 200 and $httpcode > 200) {
					if (!$option['ignoreHttpCode']) {
						return core::setError(2, 'error http code', 'Http Code: '.$httpCode);
					}
				}

				if (curl_errno($curl)) {
					if ($option['saveToFile']) {
						fclose($fp);
						unlink($option['saveToFile']);
					}
					return core::setError(3, 'error download data', curl_error($curl));
				}

				if ($option['saveToFile']) {
					fclose($fp);
					return true;
				} elseif($option['JSONData'] === true) {
					return json_decode($getData, $option['JSONAssoc']);
				} else {
					return $getData;
				}
				break;
			default: //other
				if($option['saveToFile']){
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
	public function getCurrentPageURL(array $option = []) : string {
		core::setError();

		if (!isset($option['request_uri'])) {
			$option['request_uri'] = true;
		}

		if (!isset($option['dirOnly'])) {
			$option['dirOnly'] = false;
		}

		$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];

		if ($option['request_uri'] === false) {
			return $url.'/';
		}

		$url .= $_SERVER['REQUEST_URI'];

		if ($option['dirOnly']) {
			return $url = str_replace(basename($url), '', $url);
		}

		return $url;
	}
	public function getClientIP() : string {
		core::setError();

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else  {
			return $_SERVER['REMOTE_ADDR'];
		}
	}
	public function ping(string $url) : int {
		core::setError();

		$starttime = microtime(true);
		$socket = @fsockopen($url, 80, $errno, $errstr, 10);
		$stoptime = microtime(true);

		if (!$socket) {
			return -1;
		}

		fclose($socket);
		
		return floor(($stoptime - $starttime) * 1000);
	}
	public function getHeader(string $name){
		core::setError();

		$headers = apache_request_headers();

		return isset($headers[$name])?null:$headers[$name];
	}
	public function getBrowserInfo() : array {
		core::setError();

		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$platform = 'Unknown';
        $platformList = [
            'Android' => 'Android',
            'Iphone' => 'iOS',
            'Linux' => 'Linux',
            'macintosh|mac os x' => 'Mac',
            'windows|win32' => 'Windows',
            'Mobile' => 'Mobile'
        ];

        foreach ($platformList as $name => $value) {
            if (preg_match('/'.$name.'/i', $userAgent)) {
                $platform = $value;
                break;
            }
		}
		
		$browser = 'Unknown';
        $version = 'Unknown';
        $browserList = [
            ['Edg|Edge', 'Edge', ['Edg\/', 'Edge\/']],
            ['OPR|Opera', 'Opera', ['OPR\/', 'Version\/', 'Opera\/']],
            ['MSIE', 'Internet Explorer', ['MSIE ', 'rv:']],
            ['GSA', 'GSA', ['GSA\/']],
            ['UCBrowser', 'UCBrowser', ['UCBrowser\/']],
            ['SamsungBrowser', 'SamsungBrowser', ['SamsungBrowser\/']],
            ['YaBrowser', 'Yandex', ['YaBrowser\/']],
            ['Mobile Safari', 'Mobile Safari', ['Mobile Safari\/']],
            ['Camino', 'Camino', ['Camino\/']],
            ['SeaMonkey', 'SeaMonkey', ['SeaMonkey\/']],
            ['Firefox', 'Firefox', ['Firefox\/']],
            ['Vivaldi', 'Vivaldi', ['Vivaldi\/']],
            ['Chromium', 'Chromium', ['Chromium\/']],
            ['Chrome', 'Google Chrome', ['Chrome\/']],
            ['Kindle', 'Kindle', ['Kindle\/']],
            ['OmniWeb', 'OmniWeb', ['OmniWeb\/']],
            ['Safari', 'Safari', ['Safari\/', 'Version\/']],
            ['Trident', 'Internet Explorer', ['rv:', 'Trident\/']],
            ['Netscape', 'Netscape', ['Netscape\/']],
            ['K-Meleon', 'K-Meleon', ['K-Meleon\/']],
            ['AppleWebKit', 'AppleWebKit', ['AppleWebKit\/']],
            ['Gecko', 'Gecko', ['Gecko\/']],
            ['NetSurf', 'NetSurf', ['NetSurf\/']],
            ['bot|crawl|slurp|spider|mediapartners', 'BOT', ['Version\/', 'Googlebot\/']],
            //['', '', ['\/']],
        ];

        foreach ($browserList as $data) {
            if (preg_match('/'.$data[0].'/i',$userAgent)) {
                $browser = $data[1];

                foreach ($data[2] as $search) {
                    preg_match('/('.$search.')+([0-9.]+)/m', $userAgent, $matches);

                    if (count($matches) === 3) {
                        $version = $matches[2];
                        break;
                    }
                }

                break;
            }
		}

		return [
			'userAgent' => $userAgent,
			'browser' => $browser,
			'version' => $version,
			'platform' => $platform
		];
	}
	private function _getDefaultOptionForGet($option) : array{
		core::setError();

		if (!isset($option['userAgent'])) {
			$option['userAgent'] = 'FourFramework ('.core::$info['version'].'/Library:'.$this->version.')';
		}

		if (!isset($option['timeout'])) {
			$option['timeout'] = 1000;
		}

		if (!isset($option['ignoreHttpCode'])) {
			$option['ignoreHttpCode'] = false;
		}

		if (!isset($option['JSONData'])) {
			$option['JSONData'] = false;
		}
		
		if (!isset($option['JSONAssoc'])) {
			$option['JSONAssoc'] = true;
		}
		
		if(!isset($option['saveToFile'])) {
			$option['saveToFile'] = false;
		}
		
		return $option;
	}
};
?>