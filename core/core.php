<?php
class core{
	//konfiguracja
	public $config;
	//wersja rdzenia frameworka
	public $version = '0.0.4 Alpha';
	//zmienne dla modułów
	public $module;
	public $module_list = Array();
	public $module_config = Array();
	//zmienne dla modeli
	public $model;
	public $model_list = Array();
	//zmienne dla template
	public $template_dir = 'template/';
	public $template_extension = '.inc.tpl';
	//zmienne dla logów
	public $log_save = true;
	public $log_show = Array(
		'message' => false, //wyświetlenie informacji w logach
		'error' => true //wyświetlanie błędów w logach
	);
	//powroty (../) do folderu głównego
	public $reversion = '';
	//Funkcja główna
	public function __construct(){
		$this->config = include('config.php');
		//jeżeli logi włączone to ich wyświetlenie
		if($this->config['error'] == true) ini_set('error_reporting', E_ALL);
		//automatyczne tworzenie ścieżki dla zmiennej reversion
		for($i=0; $i<=100; $i++){
			// sprawdzanie czy plik istnieje
			if(file_exists($this->reversion."core/core.php")) break;
			// wpisanie powrotu folderu do zmiennej
			$this->reversion .= "../";
		}
	}
	//Ładowanie pliku widoku (folder view/)
	public function loadView($name, $dir = "view/"){
		//tworzenie ścieżki
		$path = $dir.$name.'.php';
		//jeżeli plik nie istnieje
		if(!file_exists($path)) $this->_fatalError('Error loading view file on path: '.$path.' (core)');
		//dodanie logu do pliku
		$this->log_message('Success loading view file on path: '.$path.' (core.php)');
		//wczytywanie pliku
		require($path);
	}
	//Ładowanie pliku modułu (folder module/)
	public function loadModule($name){
		//tworznie scieżki do folderu modułu
		$path = $this->reversion.'module/'.$name.'/';
		//sprawdzenie czy moduł jest już załadowany
		if(!$this->checkModule($name)){
			//ścieżka do konfiguracji
			$path_config = $path."config.php";
			//sprawdzanie czy plik z konfiguracją istnieje
			if(!file_exists($path_config)) $this->_fatalError('Error loading module '.$name.' on path: '.$path.' (core)');
			//ładowanie konfiguracji
			$config = include($path_config);
			//wczytywanie konfiguracji modułu do tablicy
			$this->module_config[$name] = $config;
			$this->module_config[$name]['path'] = $path;
			//sprawdzenie czy w konfiguracji modułu są dane 'name'
			if(!isset($config['name'])) $this->module_config[$name]['name'] = $name;
			//wczytywanie wybranych plików
			foreach($config['include'] as $file) $this->_include($path.$file);
			//ścieżka do głównego pliku z klasą
			$path_mainFile = $path.$config['main_file'];
			//sprawdzanie czy plik z klasą istnieje
			if(file_exists($path_mainFile)){
				//wczytywanie klasy
				include($path_mainFile);
				//dodawanie modułu do tablicy
				$this->module[$name] = new $config['main_class_name']($this, $this->module_config[$name]);
			};
			//dodawanie modułu do listy modułów
			array_push($this->module_list, $name);
			//informacja o poprawnie załadowanym module
			$this->log_message('Success loading module '.$name.' on path: '.$path.' (core.php)');
			return true;
		}else return false;
	}
	//Usunięcie załadowanego modułu
	public function unloadModule($name){
		//sprawdzanie czy modół istnieje
		if(!$this->checkModule($name)) return false;
		//wyszukiwanie modułu w liście modułów
		$id = array_search($name,  $this->model_list);
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
		//sprawdzanie czy model nie jest już wczytany
		if(!in_array($name, $this->model_list)){
			//generowanie ścieżki do pliku
			$path = $dir.$name.'.php';
			//jeżli plik nie istnieje
			if(!file_exists($path)) $this->_fatalError('Error loading model file on path: '.$path.' (core)');
			//log o pozytywnym załadowaniu modelu
			$this->log_message('Success loading model file on path: '.$path.' (core.php)');
			//wczytywanie pliku modeli
			$this->_include($path);
			//tworzenie nazwy dla klasy
			$modelName = $name."Model";
			//dodawanie modeli do tablicy
			$this->model[$name] = new $modelName($this);
			//dodawanie nazwy modeli do listy
			array_push($this->model_list, $name);
			return true;
		}else return false;
	}
	//Ładowanie kontrolera (folder controller/)
	public function loadController($name, $dir = "controller/"){
		//tworzenie ścieżki do pliku
		$path = $dir.$name.'.php';
		//jeżeli plik nie istnieje
		if(!file_exists($path)) $this->_fatalError('Error loading controller file on path: '.$path.' (core)');
		//log o pozytywnym załadowaniu modelu
		$this->log_message('Success loading controller file on path: '.$path.' (core.php)');
		//wczytywanie pliki
		$this->_include($path);
		//wykonanie kontrolera
		new $name($this);
	}
	//Ładowanie szablonu strony (folder template/)
	public function Template($file, $dir = -1, $ext = -1){
		//przybranie wartości domyślnych
		if($dir == -1) $dir = $this->template_dir;
		//przybranie wartości domyślnych
		if($ext == -1) $ext = $this->template_extension;
		//tworzenie ścieżki do pliku
		$path = $dir.$file.$ext;
		//jeżeli plik nie istnieje
		if(!file_exists($path)) $this->_fatalError('Error loading template file on path: '.$path.' (core)');
		//pobieranie treści pliku do zmiennej
		$data = file_get_contents($path);
		//konwersja danych szablonu
		foreach($this->config['array_template'] as $text => $content){
			$data = str_replace("{\$".$text."\$}", $content, $data);
		}
		//konwersja danych których nie ma na liście
		$data = preg_replace('({\$(.*?)\$})', "", $data);
		//dodanie logu
		$this->log_message('Success loading template file on path: '.$path.' (core.php)');
		//wyświetlenie szablonu
		echo $data;
	}
	//Ładowanie danych do szablonu np.
	public function templateSet($name, $value, $edit=true){
		//jeżeli $edit==1 oraz dane już istnieją
		if(in_array($name, $this->config['array_template_list']) and $edit==true) $this->config['array_template'][$name] .= $value;
		//tworzenie nowych danych
		else{
			//aktualizacja danych
			$this->config['array_template'][$name] = $value;
			//dodanie danych do tablicy
			array_push($this->config['array_template_list'], $name);
		}
	}
	//funkcja wypisująca logi
	private function writelog($string, $type){
		//sprawdzenie czy logi są uruchomione
		if($this->log_save == false) return false;
		//ścieżka do folderu z logami
		$path = $this->reversion.$this->config['log_dir'];
		//sprawdzenie czy plik logu już istnieje
		if(!file_exists($path)) mkdir($path, 0644, true);
		//tworzenie ścieżki do pliku logu
		$path = $path.$this->config['log_file'];
		//tablica z ciągem do zmiany
		$replace = Array('year' => date('Y'), 'month' => date('m'), 'day' => date('d'), 'hour' => sprintf('%02d', date('G')), 'min' => date('i'), 'sec' => date('s'), 'type' => $type, 'string' => $string);
		//tekst który będzie w lini
		$write = $this->config['log_string'];
		//otwarcie pliku
		$open = fopen($path, "a+") or die('<p>error open file '.$path.'</p>');
		//zamiana danych zapisywanych do zmiennej
		foreach($replace as $data => $string) $write = str_replace("{".$data."}", $string, $write);
		//wpisanie danych do pliku
		fwrite($open, $write);
		//zamykanie plików
		fclose($open);
	}
	//log z błędem
	public function log_error($string){
		//sprawdzanie czy wpisywanie logów błędów jest uruchomione
		if($this->log_show['error'] == false) return false;
		//wpisanie logu do pliku
		return $this->writelog($string, 'error');
	}
	//log z wiadomością
	public function log_message($string){
		//sprawdzanie czy wpisywanie logów informacji jest uruchomione
		if($this->log_show['message'] == false) return false;
		//wpisanie logu do pliku
		return $this->writelog($string, 'message');
	}
	//w przypadku błędów krytycznych
	public function _fatalError($string){
		//wpisanie logu błędu do pliku
		$this->log_error($string);
		//zakończenie ładowania strony i wyświetlenie błędu
		die('<b>Fatal error:</b><br /><i>'.$string.'</i>');
	}
	//wczytywanie pliku jeżeli istnieje
	private function _include($path){
		//sprawdzanie czy plik istnieje i jeżeli tak to wczytywanie go
		if(is_file($path)) return include($path);
		//zwracanie false jeżeli błąd
		return false;
	}
}