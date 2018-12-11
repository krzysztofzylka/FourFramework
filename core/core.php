<?php
class core{
	//wersja programu
	public $version = '0.1.4 Alpha';
	public $releaseDate = '11.12.2018';
	//Funkcja główna
	public function __construct(){
		// $this->config = include('config.php');
		include('variable.php');
		//jeżeli logi włączone to ich wyświetlenie
		if($this->error == true){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('default_enable', 1);
		}
		//jeżeli logi błędów php mają być zapisywane do pliku
		if($this->php_error == true){
			$dir = $this->reversion.$this->php_error_dir.$this->php_error_file;
			ini_set("error_log", $dir);
		}
		//automatyczne tworzenie ścieżki dla zmiennej reversion
		for($i=0; $i<=100; $i++){
			// sprawdzanie czy plik istnieje
			if(file_exists($this->reversion."core/core.php")) break;
			// wpisanie powrotu folderu do zmiennej
			$this->reversion .= "../";
		}
		//uruchamianie rozszerzeń
		$this->_startExtension();
	}
	//Ładowanie pliku widoku (folder view/)
	public function loadView($name, $dir = "view/"){
		//tworzenie ścieżki
		$path = $this->reversion.$dir.basename($name).'.php';
		//jeżeli plik nie istnieje
		if(!is_file($path)) return $this->wlog('Error loading view file on path: '.$path, 'core', 'error');
		//dodanie logu do pliku
		$this->wlog('Success loading view file on path: '.$path, 'core', 'message');
		//wczytywanie pliku
		return require($path);
	}
	//Ładowanie pliku modułu (folder module/)
	public function loadModule($name){
		//zabezpieczenie nazwy
		$name = basename($name);
		//tworzenie ścieżek
		$path = $this->reversion.'module/'.$name.'/'; //ścieżka do modułu
		$path_config = $path."config.php"; //ścieżka do konfiguracji
		//błąd jeżeli moduł jest już zalogowany
		if($this->checkModule($name))
			return wlog('module \''.$name.'\' is alerty exists', 'core', 'error');
		if(!is_file($path_config))
			$this->wlog('error loading module '.$name.' on path: '.$path, 'core', 'error');
		//ładowanie konfiguracji do tablicy
		$config = include($path_config);
		$config['path'] = $path;
		if(!isset($config['name']) or $config['name'] == '') $config['name'] = $name;
		$this->module_config[$name] = $config;
		//sprawdzanie czy moduł wymaga dodatkowych modułów
		if(isset($config['module_include']))
			foreach($config['module_include'] as $requiremodule)
				if(!$this->checkModule($requiremodule))
					return $this->wlog('Error loading module '.$name.', you must include and configurate other module: '.$requiremodule, 'core', 'error');
		//wczytywanie wybranych plików
		foreach($config['include'] as $file) require_once($path.$file);
		//pobieranie klasy
		$className = $this->_loadClassFromFile($path.$config['main_file']);
		$this->module[$name] = new $className($this, $config);
		//dodawanie modułu do listy modułów
		array_push($this->module_list, $name);
		//informacja o poprawnie załadowanym module
		$this->wlog('Success loading module '.$name.' on path: '.$path, 'core', 'message');
		return $this->module[$name];
	}
	//Usunięcie załadowanego modułu
	public function unloadModule($name){
		//sprawdzanie czy modół istnieje
		if(!$this->checkModule($name)) return false;
		//wyszukiwanie modułu w liście modułów
		$id = array_search($name, $this->model_list);
		//usuwanie modułu z tablic i list 
		unset($this->module_list[$id], $this->module_config[$name], $this->module[$name]);
		return true;
	}
	//Sprawdzanie czy moduł jest załadowany
	public function checkModule($name){
		//zwracanie wartosci czy moduł jest na liście
		return in_array($name, $this->module_list);
	}
	//Ładowanie modelu (folder model/)
	public function loadModel($name, $dir = "model/"){
		//zabezpieczenie pliku
		$name = basename($name);
		//sprawdzanie czy model nie jest już wczytany
		if(!in_array($name, $this->model_list)){
			//generowanie ścieżki do pliku
			$path = $this->reversion.$dir.$name.'.php';
			//jeżli plik nie istnieje
			if(!is_file($path)) return $this->wlog('Error loading model file on path: '.$path, 'core', 'message');
			//ładowanie klasy
			$className = $this->_loadClassFromFile($path);
			$this->model[$name] = new $className($this);
			//dodawanie nazwy modeli do listy
			array_push($this->model_list, $name);
			//informacja o sukcesie
			$this->wlog('Success loading model file on path: '.$path, 'core', 'message');
			return $this->model[$name];
		}else return false;
	}
	//Ładowanie kontrolera (folder controller/)
	public function loadController($name, $dir = "controller/"){
		//tworzenie ścieżki do pliku
		$path = $this->reversion.$dir.basename($name).'.php';
		//jeżeli plik nie istnieje
		if(!is_file($path)) return $this->wlog('Error loading controller file on path: '.$path, 'core', 'error');
		//ładowanie klasy
		$className = $this->_loadClassFromFile($path);
		$object = new $className($this);
		//log o pozytywnym załadowaniu modelu
		$this->wlog('Success loading controller file on path: '.$path, 'core', 'message');
		return $object;
	}
	//Ładowanie szablonu strony (folder template/)
	public function Template($file, $dir = -1, $ext = -1){
		//zabezpieczenie zmiennej $file
		$file = basename($file);
		//przybranie wartości domyślnych
		if($dir == -1) $dir = $this->template_dir;
		if($ext == -1) $ext = $this->template_extension;
		//tworzenie ścieżki do pliku
		$path = $this->reversion.$dir.$file.$ext;
		//jeżeli plik nie istnieje
		if(!is_file($path)) $this->wlog('Error loading template file on path: '.$path, 'core', 'error');
		//pobieranie treści pliku do zmiennej
		$data = file_get_contents($path);
		//konwersja danych szablonu
		foreach($this->array_template as $text => $content)
			$data = str_replace("{\$".$text."\$}", $content, $data);
		//konwersja danych których nie ma na liście
		$data = preg_replace('({\$(.*?)\$})', "", $data);
		//dodanie logu
		$this->wlog('Success loading template file on path: '.$path, 'core', 'message');
		//wyświetlenie szablonu
		echo $data;
	}
	//Ładowanie danych do szablonu np.
	public function templateSet($name, $value, $edit=true){
		//jeżeli $edit==1 oraz dane już istnieją
		if(in_array($name, $this->array_template_list) and $edit==true)
			$this->array_template[$name] .= $value;
		//tworzenie nowych danych
		else{
			//aktualizacja danych
			$this->array_template[$name] = $value;
			//dodanie danych do tablicy
			array_push($this->array_template_list, $name);
		}
	}
	//funkcja debugująca
	public function __debugInfo() {
        return [
			'version' => $this->version,
			'reversion' => $this->reversion==''?'***none***':$this->reversion,
			'error' => [
				'show_error' => $this->error==true?'true':'false',
				'php_error' => [
					'active' => $this->php_error==true?'true':'false',
					'dir' => $this->php_error_dir,
					'file' => $this->php_error_file,
				],
			],
			'module' => [
				'count' => count($this->module_list),
				'list' => empty($this->module_list)==true?'***empty***':$this->module_list,
			],
			'model' => [
				'count' => count($this->model_list),
				'list' => empty($this->model_list)==true?'***empty***':$this->model_list,
			],
			'template' => [
				'dir' => $this->template_dir,
				'extension' => $this->template_extension,
			],
			'log' => [
				'save' => $this->log_save==true?'true':'false',
				'dir' => $this->log_dir,
				'file' => $this->log_file,
				'hidden_type' => $this->log_hide_type,
			],
			'api' => [
				'secure' => $this->API_secure?'true':'false',
				'url' => $this->API,
			],
			'extension' => [
				'db',
				[
					'nazwa' => 'moduleManager',
					'debug' => $this->moduleManager->__debugInfo()
				],
				[
					'nazwa'=>'test',
					'debug'=> $this->test->__debugInfo()
				],
			]
		];
    }
	//wpisanie logu
	public function wlog($value, $name=-1, $type=-1){
		//jeżeli logi wyłączone
		if($this->log_save==false) return false;
		//anulowanie dodawania wybranych typów logów
		if(in_array($type, $this->log_hide_type)) return false;
		//ciąg do zapisania
		$string = '['.date('Y.m.d h:m:s.v').'] ['.$name.'] ['.$type.'] ['.$value.']'.PHP_EOL;
		//ścieżka do pliku
		$path = $this->reversion.$this->log_dir.$this->log_file;
		//jeżeli plik nie istnieje to utworzenie go
		if(!file_exists($path)) touch($path);
		//zapis do pliku
		return file_put_contents($path, $string, FILE_APPEND);
	}
	//pobieranie klasy i zwracanie jej nazwy
	private function _loadClassFromFile($path){
		//pobieranie tablicy z klasami
		$cln = get_declared_classes();
		//wczytanie pliku
		include($path);
		// wyszukiwanie wczytanego objektu
		$classname = array_diff(get_declared_classes(), $cln);
		//sprawdzanie czy jakaś klasa została znaleziona
		if(count($classname) ==  0) return $this->wlog('error find object in file, path: \''.$path.'\'', 'core', 'error');
		//zwracanie nazwy
		return $classname[key($classname)];
	}
	//uruchamianie rozszerzenia
	private function _startExtension(){
		//rozszerzenie bazy danych
		include($this->reversion.'core/extension/db/db.php');
		$this->db = new core_db_bakj98D($this);
		//rozszerzenie menadżera modułów
		include($this->reversion.'core/extension/moduleManager/moduleManager.php');
		$this->moduleManager = new core_moduleManager_hdyT53gA($this);
		//rozszerzenie testowania
		include($this->reversion.'core/extension/test/test.php');
		//zwracanie objektu
		
		$this->test = new test_g5hAGth($this);
	}
	//funkcja połączeniowa z API
	public function _API($script=''){
		//zabezpieczenie skryptu
		$script = htmlspecialchars($script);
		//rozdzielenie danych
		$dane = [];
		$explode = explode(';', $script);
		foreach($explode as $exp){
			$value = explode('=', $exp, 2);
			array_push($dane, $value);
		}
		//generowanie genych wysłanych do API
		$get = '?';
		//pętla danych
		foreach($dane as $value){
			//sprawdzanie danych
			switch($value[0]){
				//wyszukiwanie po nazwie
				case 'search':
					//dodawanie wyszukiwania po nazwie
					$get .= 'search='.$value[1].'&';
					break; //kontynuacja
				//wyszukiwanie po unikalnym ID (może być tylko jeden)
				case 'uid':
					//generowanie wyszukiwania po uid
					$get = '?uid='.$value[1];
					break 2; //anulowanie dalszego wyszukiwania
			};
		}
		//poprawa danych GET
		if(substr($get, strlen($get)-1) == '&') $get = substr($get, 0, strlen($get)-1);
		//generowanie linku
		$url = $this->API.$get;
		//sprawdzanie metody wysyłania do API
		$metod = 0; //jeżeli błąd zostanie 0
		if(function_exists('curl_version')) $metod = 1; //jeżeli curl
		elseif(function_exists('file_get_contents')) $metod = 2; //jeżeli file_get_contents
		//pobieranie danych
		switch($metod){
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
				//zwracanie zdekodowanych danych
				return json_decode($getData, true);
			//file_get_contents
			case 2:
				//pobieranie i dekodowanie danych
				$getData = json_decode(file_get_contents($url), true);
				return $getData;
		}
		//błąd
		return false;
	}
}