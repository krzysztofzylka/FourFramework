<?php
class core{
	//wersja programu
	public $version = '0.1.8 Alpha';
	public $releaseDate = '31.12.2018';
	//Funkcja główna
	public function __construct(){
		require_once('variable.php');
		//jeżeli logi włączone to ich wyświetlenie
		if($this->error){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('default_enable', 1);
		}
		//jeżeli logi błędów php mają być zapisywane do pliku
		if($this->php_error){
			$dir = $this->reversion.$this->php_error_dir.$this->php_error_file;
			ini_set("error_log", $dir);
		}
		//automatyczne tworzenie ścieżki dla zmiennej reversion
		$this->__getReversion();
		//uruchamianie bibliotek
		$this->library = include($this->reversion.'core/library.php');
		//uruchamianie rozszerzeń
		$this->_startExtension();
	}
	//Ładowanie pliku widoku (folder view/)
	public function loadView($name, $dir = "view/"){
		//sprawdzanie błędów
		try{
			//tworzenie ścieżki
			$path = $this->reversion.$dir.basename($name).'.php';
			//jeżeli plik nie istnieje
			if(!is_file($path)) return $this->wlog('Error loading view file on path: '.$path, 'core', 'error');
			//dodanie logu do pliku
			$this->wlog('Success loading view file on path: '.$path, 'core', 'message');
			//wczytywanie pliku
			return require($path);
		//jeżeli błąd
		}catch (Exception $e){
			return $this->wlog('error in loadView: '.$e->getMessage(), 'core', 'error');
		}
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
			return $this->wlog('module \''.$name.'\' is alerty exists', 'core', 'message');
		if(!is_file($path_config))
			return $this->wlog('error loading module '.$name.' on path: '.$path, 'core', 'error');
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
		$className = $this->_loadClassFromFile($path.$config['main_file'], $config);
		$this->module[$name] = new $className($this, $config);
		//dodawanie modułu do listy modułów
		array_push($this->module_list, $name);
		//informacja o poprawnie załadowanym module
		$this->wlog('Success loading module '.$name.' on path: '.$path, 'core', 'message');
		//zwracanie modułu
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
			//zwracanie modelu
			return $this->model[$name];
		//jeżeli błąd
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
	public function Template($file, $dir = null, $ext = null){
		//zabezpieczenie zmiennej $file
		$file = basename($file);
		//przybranie wartości domyślnych
		$dir = $dir ?? $this->template_dir;
		$ext = $ext ?? $this->template_extension;
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
		return $data;
	}
	//Ładowanie danych do szablonu np.
	public function templateSet($name, $value, $edit=true){
		//jeżeli $edit==1 oraz dane już istnieją
		if(in_array($name, $this->array_template_list) and $edit){
			$this->array_template[$name] .= $value;
			return true;
		//tworzenie nowych danych
		}else{
			//aktualizacja danych
			$this->array_template[$name] = $value;
			//dodanie danych do tablicy
			array_push($this->array_template_list, $name);
			return true;
		}
		//jeżeli błąd
		return false;
	}
	//funkcja debugująca
	public function __debugInfo() {
		$class = $this->library->class;
		$classList = [];
		foreach($this->module_list as $name){
			$className = get_class($this->module[$name]);
			$classList['m_'.$name] = $class->is_anonymous($className)?'*** anonymous ***':$className;
		}
		foreach($this->model_list as $name){
			$className = get_class($this->model[$name]);
			$classList['mo_'.$name] = $class->is_anonymous($className)?'*** anonymous ***':$className;
		}
		$className = get_class($this->db);
		$classList['e_db'] = $class->is_anonymous($className)?'*** anonymous ***':$className;
		$className = get_class($this->moduleManager);
		$classList['e_moduleManager'] = $class->is_anonymous($className)?'*** anonymous ***':$className;
		$className = get_class($this->test);
		$classList['e_test'] = $class->is_anonymous($className)?'*** anonymous ***':$className;
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
					'name' => 'moduleManager',
					'debug' => $this->moduleManager->__debugInfo()
				],
				[
					'name'=>'test',
					'debug'=> $this->test->__debugInfo()
				],
			],
			'library' => [
				'count' => count($this->library->__list),
				'list' => $this->library->__list,
			],
			'classList' => $classList,
		];
    }
	//wpisanie logu
	public function wlog($value, $name=null, $type=null){
		//jeżeli logi wyłączone
		if(!$this->log_save) return false;
		//anulowanie dodawania wybranych typów logów
		if(in_array($type, $this->log_hide_type)) return false;
		//ciąg do zapisania
		$string = '['.date('Y.m.d h:m:s.v').'] ['.$name.'] ['.$type.'] ['.$value.']'.PHP_EOL;
		//ścieżka do pliku
		$path = $this->reversion.$this->log_dir.$this->log_file;
		//jeżeli plik nie istnieje to utworzenie go
		if(!file_exists($path)) touch($path);
		//zapis do pliku
		for($i=0;$i<5;$i++){
			$save = @file_put_contents($path, $string, FILE_APPEND);
			if($save != false) break;
		}
		return true;
	}
	//pobieranie klasy i zwracanie jej nazwy
	private function _loadClassFromFile($path, $config=null){
		//sprawdzanie błędów
		try{
			//pobieranie tablicy z klasami
			$cln = get_declared_classes();
			//wczytanie pliku
			$includeClass = include($path);
			// wyszukiwanie wczytanego objektu
			$classname = array_diff(get_declared_classes(), $cln);
			//sprawdzanie czy jakaś klasa została znaleziona
			if(count($classname) ==  0) return $this->wlog('error find object in file, path: \''.$path.'\'', 'core', 'error');
			//zwracanie nazwy
			return $classname[key($classname)];
		//jeżeli błąd
		}catch (Exception $e){
			return $this->wlog('error in _loadClassFromFile: '.$e->getMessage(), 'core', 'error');
		}
	}
	//uruchamianie rozszerzenia
	private function _startExtension(){
		//rozszerzenie bazy danych
		$this->db = require_once($this->reversion.'core/extension/db/db.php');
		//rozszerzenie menadżera modułów
		$this->moduleManager = require_once($this->reversion.'core/extension/moduleManager/moduleManager.php');
		//rozszerzenie testowania
		$this->test = require_once($this->reversion.'core/extension/test/test.php');
		//zwrot danych
		return;
	}
	//funkcja połączeniowa z API
	public function _API($script=''){
		//zabezpieczenie skryptu
		$script = htmlspecialchars($script);
		//rozdzielenie przyjmowanych danych
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
		//pobieranie danych
		return $this->library->network->getJSONData($url);
	}
	//wersja php
	public function _phpv($version=null){
		//jeżeli wyświetlenie funkcji
		if($version == null) return PHP_VERSION;
		//jeżeli sprawdzenie wersji
		if(PHP_VERSION >= $version) return true;
		//zwrócenie fałszu
		return false;
		
	}
	//automatyczne tworzenie ścieżki dla zmiennej reversion
	private function __getReversion(){
		for($i=0; $i<=100; $i++){
			// sprawdzanie czy plik istnieje
			if(file_exists($this->reversion."core/core.php")) return;
			// wpisanie powrotu folderu do zmiennej
			$this->reversion .= "../";
		}
	}
}