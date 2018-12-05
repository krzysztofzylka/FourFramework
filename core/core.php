<?php
class core{
	//wersja programu
	public $version = '0.1.3a Alpha';
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
		require($path);
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
			return true;
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
		new $className($this);
		//log o pozytywnym załadowaniu modelu
		$this->wlog('Success loading controller file on path: '.$path, 'core', 'message');
	}
	//Ładowanie szablonu strony (folder template/)
	public function Template($file, $dir = -1, $ext = -1){
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
			'module' => Array(
				'count' => count($this->module_list),
				'list' => empty($this->module_list)==true?'***empty***':$this->module_list,
			),
			'model' => Array(
				'count' => count($this->model_list),
				'list' => empty($this->model_list)==true?'***empty***':$this->model_list,
			),
			'template' => Array(
				'dir' => $this->template_dir,
				'extension' => $this->template_extension,
			),
			'log' => Array(
				'save' => $this->log_save==true?'true':'false',
				'dir' => $this->log_dir,
				'file' => $this->log_file,
				'hidden_type' => $this->log_hide_type,
			),
			'extension' => [
				'db', ['nazwa' => 'moduleManager', 'debug' => $this->moduleManager->__debugInfo()], ['nazwa'=>'test', 'debug'=> $this->test->__debugInfo()],
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
		$this->test = new test_g5hAGth($this);
	}
}