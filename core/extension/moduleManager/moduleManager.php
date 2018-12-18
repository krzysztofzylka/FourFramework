<?php
class core_moduleManager_hdyT53gA{
	//główne zmienne
	protected $core;
	protected $path;
	protected $path_module;
	//główna funkcja
	public function __construct($obj){
		//generowanie głównych zmiennych
		$this->core = $obj;
		$this->path = $obj->reversion.'core/extension/moduleManager/';
		$this->path_module = $obj->reversion.'module/';
		include($this->path.'variable.php');
		$this->_autostart();
		$this->_api_testconnect();
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
		include($this->path.'function/manager.php');
		include($this->path.'type/'.$this->manager['view'].'.php');
	}
	//automatyczne uruchamianie modułów
	protected function _autostart(){
		$read = $this->core->db->read('core', 'extension_moduleManager_autostart');
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
	//sprawdzanie typu przesyłania danych
	private function _api_testconnect(){
		if(function_exists('curl_version')) $this->vapi_type = 1;
		$this->vapi_type = 2;
	}
	//pobieranie danych z API
	public function api_get($name=-1, $uid=-1){
		//jeżeli api wyłączone
		if($this->vapi_use == false){
			if($this->vapi_offline){
				$read = $this->core->db->read('module_manager2_offline', $uid);
				if($read == null) return ['count' => 0];
				return unserialize($read);
			}
			return;
		}
		//jeżeli brak typu
		if($this->vapi_type == 0) return;
		$add = '';
		if($name <> -1) $add .= 'search='.$name;
		if($uid <> -1){
			if($add <> '') $add .= '&';
			$add .= 'uid='.$uid;
		}
		if($add <> '') $add = '?'.$add;
		switch($this->vapi_type){
			case 1:
				//curl
				break;
			case 2:
				$data = file_get_contents($this->vapi_url.$add);
				break;
		}
		$data = json_decode($data, true);
		if($uid <> -1 and $data['count'] == 1){
			$data['list'] = $data['list'][0];
		}
		if($this->vapi_offline) $this->core->db->write('module_manager2_offline', $uid, serialize($data));
		return $data;
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
	public function download(string $url){
		if($this->vapi_type == 0) return;
		$path = $this->path.'temp.zip';
		switch($this->vapi_type){
			case 1:
				// curl
				break;
			case 2:
				file_put_contents($path, fopen($url, 'r'));
				break;
		}
		$zip = new ZipArchive;
		if ($zip->open($path) == TRUE) {
			$zip->extractTo($this->path_module);
			$zip->close();
		}
		unlink($path);
	}
}