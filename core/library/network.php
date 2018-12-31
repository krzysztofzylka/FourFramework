<?php
//główna klasa biblioteki
return $this->network = new class($this->core){
	//funkcja z rdzeniem
	protected $core;
	//metoda pobierania danych
	private $method = 0;
	//metoda pobierania plików
	private $methodDownloadFile = 0;
	//maksymalny czas oczekiwania dla CURL
	private $curlTimeout = 1000;
	//główna funkcja
	public function __construct($obj){
		//inicjonowanie zmiennych
		$this->core = $obj;
		//pobieranie metody połączenia z zewnętrznymi stronami
		$this->_getMethod();
	}
	//pobranie metody połączenia z serwerem w celu pobrania danych
	private function _getMethod(){
		///--- dla pobierania danych
		//jeżeli curl
		if(function_exists('curl_version'))
			$this->method = 1;
		//jeżeli file_get_contents
		elseif(function_exists('file_get_contents'))
			$this->method = 2;
	}
	//pobranie danych z url
	public function getData($url){
		switch($this->method){
			//błąd pobierania danych
			case 0:
				//zwracanie błędu
				return false;
				break;
			//curl
			case 1:
				//inicjonowanie curl
				$curl = curl_init();
				//konfiguracja curl
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_TIMEOUT, $this->curlTimeout);
				//pobieranie danych
				$getData = curl_exec($curl);
				//jeżeli błąd
				if($getData === false){
					//jeżeli błąd
					$this->core->wlog('Error download data, error: '.curl_error($ch), 'library network', 'error');
					return false;
				}
				//zamykanie curl
				curl_close($curl);
				//zwracanie danych
				return $getData;
			//file_get_contents
			case 2:
				//pobieranie i zwracanie danych
				$getData = file_get_contents($url);
				return $getData;
		}
		//błąd
		return false;
	}
	//pobieranie danych JSON z url
	public function getJSONData($url){
		//pobieranie danych
		$readData = $this->getData($url);
		//jeżeli błąd
		if(!$readData) return false;
		//jeżeli sukces to dekodowanie i zwracanie danych
		return json_decode($readData, true);
	}
	//pobieranie pliku
	public function downloadFile($url, $path){
		//jeżeli plik w podaniej ścieżce już istnieje
		if(file_exists($path)) return false;
		//pobieranie plików
		switch($this->method){
			//jeżeli błąd
			case 0:
				return false;
				break;
			//dla curl
			case 1:
				$fp = fopen($path, 'w');
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_TIMEOUT, $this->curlTimeout);
				$data = curl_exec($ch);
				curl_close($ch);
				if(curl_errno($ch)){
					fclose($fp);
					unlink($path);
					$this->core->wlog('Error download file, error: '.curl_error($ch), 'library network', 'error');
					return false;
				}
				fclose($fp);
				return true;
				break;
			//dla file_put_contents
			case 2:
				//pobieranie plików
				$download = file_put_contents($path, fopen($url, 'r'));
				//jeżeli błąd
				if(!$download) return false;
				//jeżeli sukcess
				return true;
				break;
		}
		//jeżeli błąd
		return false;
	}
	//Pobieranie linka do aktualnej strony
	public function getCurrentPageURL(){
		$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
		$url .= ( $_SERVER["SERVER_PORT"] <> 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		$url .= $_SERVER["REQUEST_URI"];
		return $url;
	}
	//pobieranie adresu IP klienta
	public function getClientIP(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return  $_SERVER['HTTP_X_FORWARDED_FOR'];
		else return $_SERVER['REMOTE_ADDR'];
	}
};
?>