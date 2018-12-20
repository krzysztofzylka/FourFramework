<?php
return new class($this){
	protected $core;
	protected $path = '';
	public function __construct($obj){
		$this->core = $obj;
		$this->path = $obj->reversion.'core/extension/db/base/';
		if(!file_exists($this->path)) mkdir($this->path);
	}
	//zapis danych do bazy danych
	public function write($db_name, $name, $value){
		//odczytywanie pliku, jeżeli nie istnieje to tworzenie pustej tablicy
		$read = $this->_readFile($db_name);
		if($read == false) $read = array();
		//zapis danych do tablicy
		$read[$name] = $value;
		//zapis dantch do pliku
		return $this->_saveFile($db_name, $read);
	}
	//odczytanie danych z bazy
	public function read($db_name, $name, $default=null){
		//odczytanie pliku
		$read = $this->_readFile($db_name);
		if($read == false) return $default==null?false:$default;
		//jeżeli dane istnieją
		if(isset($read[$name])) return $read[$name];
		//jeżeli dane nie istnieją
		return $default;
	}
	//usunięcie danych z bazy danych
	public function del($db_name, $name){
		//odczytanie pliku
		$read = $this->_readFile($db_name);
		if($read == false) return false; //jeżeli błąd odczytywania bazy danych
		//usuwanie danych
		unset($read[$name]);
		//jeżeli w bazie nie ma więcej danych to usunięcie pliku bazy danych
		if(count($read) == 0) return unlink($file);
		//jeżeli jakieś dane jeszcze są to zapisanie pozostałych danych do bazy danych
		return $this->_saveFile($db_name, $read);
	}
	//sprawdzenie czy dane istnieją w bazie danych
	public function check($db_name, $name){
		//odczytanie pliku
		$read = $this->_readFile($db_name);
		if($read == false) return false;
		//zwrócenie informacji czy plik istnieje
		return isset($read[$name])?true:false;
	}
	//zapisanie tablicy do pliku bazy danych
	private function _saveFile($db_name, $array){
		//gerowanie ścieżki do pliku bazy
		$file = $this->path.$db_name.'.php';
		//jeżeli plik nie istnieje
		if(!is_file($file)){
			//tworzenie pliku
			touch($file);
			//ustalanie praw dostępu na tylko serwer
			chmod($file, 0600);
		}
		//kodowanie tablicy
		$data = '<?php return \''.serialize($array).'\' ?>';
		//zapis do pliku (+ informacja jeżeli niepowodzenie)
		if(!file_put_contents($file, $data)) $this->core->wlog('Error save string to file \''.$file.'\' data: \''.$data.'\' (before: \''.serialize($array).'\')', 'error', 'db');
		//dodanie logu o sukcesie
		else $this->core->wlog('Add data to database \''.$db_name.'\' data: \''.$data.'\'', 'db', 'message');
		//zwracanie informacji o powodzeniu
		return true;
	}
	//odczytanie pliku
	private function _readFile($db_name){
		//gerowanie ścieżki do pliku bazy
		$file = $this->path.$db_name.'.php';
		//błąd jeżeli plik nie isnieje
		if(!is_file($file)) return false;
		//wczytanie pliku
		$read = include($file);
		//odczytywanie zakodowanych danych z pliku
		$decode = unserialize($read);
		//błąd jeżeli dane to nie tablica lub odczytane dane są puste
		if(!is_array($decode) or $read == '') return false;
		//log o sukcesie odczytania danych
		$this->core->wlog('Read data from database \''.$db_name.'\' data: \''.$read.'\'', 'db', 'message');
		//zwrócenie tablicy
		return $decode;
	}
}
?>