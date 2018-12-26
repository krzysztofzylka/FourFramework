<?php
return new class($this){
	//główne zmienne
	protected $core;
	protected $path;
	protected $path_module;
	protected $network;
	//główna funkcja
	public function __construct($obj){
		//generowanie głównych zmiennych
		$this->core = $obj;
		$this->path = $obj->reversion.'core/extension/moduleManager/';
		$this->path_module = $obj->reversion.'module/';
		$this->network = $obj->library->network;
		include($this->path.'variable.php');
		$this->_autostart();
	}
	//pobieranie listy modułów
	//0: wszystkie, 1: aktywne
	public function get_list(int $type=0) : array{
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
					//jeżel dane to nie folder
					if(strpos($path, '.') > 0) continue;
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
	public function get_debug(string $name){
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
			echo 'Moduł nie posiada funkcji debugującej (__debugInfo)';
		}
	}
	//wyświetlenie panel administracyjny modułu
	public function get_adminpanel(string $name){
		$error = false;
		//pobieranie elementu
		if(in_array($name, $this->core->module_list)){
			$config = $this->core->module_config[$name];
		}
		//jeżeli jest funkcja debugowania
		if(isset($config['adminpanel'])){
			include($config['path'].$config['adminpanel']);
		}else{
			echo 'Moduł nie posiada panelu administracyjnego';
		}
	}
	//generowanie szablonu debugowania
	public function get_debug_style($array){
		if(is_array($array)) {
			echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>";
			echo '<tr><td colspan=2 style="background-color:#333333;padding:2px"><strong><font color=white>ARRAY</font></strong></td></tr>';
			foreach ($array as $k => $v) {
					echo '<tr><td valign="top" style="width:40px;background-color:#F0F0F0; padding:2px">';
					echo '<strong>' . $k . "</strong></td><td style='padding:2px'>";
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
		//czytanie funkcji menadżera
		include($this->path.'function/manager.php');
		//wczytanie menadżera
		include($this->path.'type/'.$this->manager['view'].'.php');
	}
	//automatyczne uruchamianie modułów
	protected function _autostart(){
		//pobieranie modułów do autostartu
		$read = $this->core->db->read('core', 'extension_moduleManager_autostart');
		//wczytywanie modułów w pętli
		foreach(explode(',', $read) as $name) if($name <> '') $this->core->loadModule($name);
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
		return [
			'debug' => [
				'style' => $this->debug['style']?'true':'false',
			],
			'manager' => [
				'view' => $this->manager['view'],
				'getData' => $this->urlGetData,
			],
			'api' => [
				'active' => $this->vapi_use?'true':'false'
			],
		];
	}
}
?>