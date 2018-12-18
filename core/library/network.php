<?php
//główna klasa biblioteki
class core_library_network_hdf5T2a{
	//funkcja z rdzeniem
	protected $core;
	//metoda pobierania danych
	private $method = 0;
	//główna funkcja
	public function __construct($obj){
		//inicjonowanie zmiennych
		$this->core = $obj;
		//pobieranie metody połączenia z zewnętrznymi stronami
		$this->_getMethod();
	}
	//pobranie metody połączenia z serwerem w celu pobrania danych
	private function _getMethod(){
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
				//pobieranie danych
				$getData = curl_exec($curl);
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
		if($readData == false) return false;
		//jeżeli sukces to dekodowanie i zwracanie danych
		return json_decode($readData, true);
	}
};
$this->network = new core_library_network_hdf5T2a($this->core);
return $this->network;
?>