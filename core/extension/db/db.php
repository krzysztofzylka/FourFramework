<?php
return new class($this){
	protected $core;
	public $path = '';
	public $temp = true;
	protected $temp_array = [];
	public function __construct($obj){
		$this->core = $obj;
		$this->path = $obj->reversion.'core/extension/db/base/';
		if(!file_exists($this->path)) mkdir($this->path);
	}
	//zapis danych do bazy danych
	public function write(string $db_name, string $name, string $value) : bool{
		//odczytywanie pliku, jeżeli nie istnieje to tworzenie pustej tablicy
		$read = $this->_readFile($db_name);
		if(!$read) $read = array();
		//zapis danych do tablicy
		$read[$name] = $value;
		//zapis dantch do pliku
		return $this->_saveFile($db_name, $read);
	}
	//odczytanie danych z bazy
	public function read(string $db_name, string $name, $default=null) : string{
		//odczytanie pliku
		$read = $this->_readFile($db_name);
		if(!$read) return $default==null?false:$default;
		//jeżeli dane istnieją
		if(isset($read[$name])) return $read[$name];
		//jeżeli dane nie istnieją
		return $default;
	}
	//usunięcie danych z bazy danych
	public function del(string $db_name, string $name) : bool{
		//odczytanie pliku
		$read = $this->_readFile($db_name);
		if(!$read) return false; //jeżeli błąd odczytywania bazy danych
		//usuwanie danych
		unset($read[$name]);
		//jeżeli w bazie nie ma więcej danych to usunięcie pliku bazy danych
		if(count($read) == 0) return unlink($file);
		//jeżeli jakieś dane jeszcze są to zapisanie pozostałych danych do bazy danych
		return $this->_saveFile($db_name, $read);
	}
	//sprawdzenie czy dane istnieją w bazie danych
	public function check(string $db_name, string $name) : bool{
		//odczytanie pliku
		$read = $this->_readFile($db_name);
		if(!$read) return false;
		//zwrócenie informacji czy plik istnieje
		return isset($read[$name])?true:false;
	}
	//zapisanie tablicy do pliku bazy danych
	private function _saveFile(string $db_name, array $array){
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
		//aktualizacja/dodawanie danych w tablicy jeżeli istnieją
		if($this->temp) $this->temp_array[$db_name] = $array;
		//zwracanie informacji o powodzeniu
		return true;
	}
	//odczytanie pliku
	private function _readFile(string $db_name){
		//jeżeli aktywne dane tymczasowe
		if($this->temp){
			//jeżeli dane są w tablicy
			if(isset($this->temp_array[$db_name])){
				//odczytanie danych do tablicy
				$read = $this->temp_array[$db_name];
				//wpisanie logu
				$this->core->wlog('Read data from database \''.$db_name.'\' (temponary)', 'db', 'message');
				//zwrócenie danych
				return $read;
			}
		}
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
		$this->core->wlog('Read data from database \''.$db_name.'\'', 'db', 'message');
		//jeżeli aktywne dane tymczasowe to dodawanie danych do tablicy
		if($this->temp) $this->temp_array[$db_name] = $decode;
		//zwrócenie tablicy
		return $decode;
	}
	//odczytanie w formie tablicy całego pliku bazy
	public function readArray(string $db_name){
		//odczytanie pliku
		$read = $this->_readFile($db_name);
		if(!$read) return false;
		//zwracanie odczytanej bazy
		return $read;
	}
}
?>