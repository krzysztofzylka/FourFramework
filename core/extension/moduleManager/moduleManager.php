<?php
class core_moduleManager_hdyT53gA{
	//główne zmienne
	protected $core;
	protected $path;
	protected $path_module;
	//główna funkcja
	public function __construct($obj){
		//generowanie głównych zmiennych1
		$this->core = $obj;
		$this->path = $obj->reversion.'core/extension/moduleManager/';
		$this->path_module = $obj->reversion.'module/';
		//wczytywanie zmiennych
		include($this->path.'variable.php');
		//autostart modułów
		$this->_autostart();
		//sprawdzanie jakie jest włączone połączenie z API
		// $this->_api_testconnect();
	}
	//pobieranie listy modułów
	//0: wszystkie, 1: aktywne
	public function get_list($type=0){
		//główna tablica
		$array = array();
		//sprawdzanie typu
		switch($type){
			//lista wszystkich pobranych modułów
			case 0:
				//skanowanie folderu z modułami
				foreach(scandir($this->path_module) as $name){
					//ścieżka do modułu
					$path = $this->path_module.$name.'/';
					//filtrowanie modułów
					if(is_file($path) or $name == '.' or $name == '..' or !file_exists($path.'config.php')) continue;
					//dodawanie modułu do tablicy
					array_push($array, $name);
				}
				return $array;
				break;
			//lista aktywnych modułów
			case 1:
				return $this->core->module_list;
				break;
		}
	}
	//wyświetlenie debugowania modułu
	public function get_debug($name){
		//pobieranie elementu
		$object = $this->core->module[$name];
		//jeżeli jest funkcja debugowania
		if(method_exists($object, '__debugInfo')){
			//jeżeli aktywne wyświetlenie stylu
			if($this->debug['style'] == true){
				$this->get_debug_style($object->__debugInfo());
			//jeżeli bez stylu
			}else{
				echo '<pre>';
				print_r($object->__debugInfo());
				echo '</pre>';
			}
		}else{
			echo 'Moduł nie posiada funkcji debugującej';
		}
	}
	//generowanie szablonu debugowania
	public function get_debug_style($array){
		if(is_array($array)) {
			echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>";
			echo '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>ARRAY</font></strong></td></tr>';
			foreach ($array as $k => $v) {
					echo '<tr><td valign="top" style="width:40px;background-color:#F0F0F0;">';
					echo '<strong>' . $k . "</strong></td><td>";
					$this->get_debug_style($v);
					echo "</td></tr>";
			}
			echo "</table>";
			return;
		}
		echo $array;
	}
	//wyświetlenie menadżera modułów
	public function manager(){
		//wczytywanie funkcji
		include($this->path.'function/manager.php');
		//wczytywanie menadżera
		include($this->path.'type/'.$this->manager['view'].'.php');
	}
	//automatyczne uruchamianie modułów
	protected function _autostart(){
		//pobieranie danych z bazy
		$read = $this->core->db->read('core', 'extension_moduleManager_autostart');
		//wczytywanie modułów
		foreach(explode(',', $read) as $name){
			if($name <> '') $this->core->loadModule($name);
		}
	}
	//funkcja generująca link
	public function generateLink($get_data=''){
		//nazwa danych get
		$get = $this->urlGetData;
		//explode
		$query = $_SERVER['QUERY_STRING'];
		$explode = explode('&', $query);
		foreach($explode as $name=>$data){
			$explode[$name] = explode('=', $data);
		}
		$search = 0;
		//wyszukiwanie i zamiana
		foreach($explode as $id => $data){
			if($data[0] == $get){
				$search = 1;
				$explode[$id][1] = $get_data;
				break;
			}
		}
		//jezeli nie znaleziono danych
		if($search == 0) array_push($explode, array($get, $get_data));
		//generowanie linka
		foreach($explode as $id => $arr) $explode[$id] = implode('=', $arr);
		//zwracanie linka
		return '?'.implode('&', $explode);
	}
	//debugowanie
	public function __debugInfo(){
		$type = $this->vapi_type;
		switch($type){
			case 0:
				$type = 'disable';
				break;
			case 1:
				$type = 'curl';
				break;
			case 2:
				$type = 'file_put_contents';
				break;
			default:
				$type = 'unknown ('.$type.')';
				break;
		}
		return [
			'debug' => [
				'style' => $this->debug['style']?'true':'false',
			],
			'manager' => [
				'view' => $this->manager['view'],
				'getData' => $this->urlGetData,
			],
			'api' => [
				'active' => $this->vapi_use?'true':'false',
				'url' => $this->vapi_url,
				'type' => $type,
			],
		];
	}
	//pobieranie nowego modulu
	public function download($url){
		//sprawdzanei czy vapi jest wybrane
		// if($this->vapi_type == 0) return;
		//generowanie ścieżki do pliku temp
		// $path = $this->path.'temp.zip';
		//pobieranie typu
		// switch($this->vapi_type){
			//curl
			// case 1:
				// curl
				// break;
			//file_put_contents
			// case 2:
				// file_put_contents($path, fopen($url, 'r'));
				// break;
		// }
		//pobieranie klasy obsługi plików ZIP
		// $zip = new ZipArchive;
		//jeżeli poprawnie uruchomiono plik
		// if ($zip->open($path) == TRUE) {
			// rozpakowywanie pliku
			// $zip->extractTo($this->path_module);
			// zamykanie archiwum
			// $zip->close();
		// }
		//usunięcie pliku tymczasowego
		// unlink($path);
	}
}