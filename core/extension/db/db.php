<?php
class core_db_bakj98D{
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
		//anulacja jeżeli zapisywane dane są takie same jak w bazie
		if($temp[$name] == $value) return true;
		//zapis danych do tablicy
		$temp[$name] = $value;
		//zapis dantch do pliku
		return $this->_saveFile($db_name, $temp);
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
		//kodowanie tablicy
		$data = '<?php return \''.serialize($array).'\' ?>';
		//zapis do pliku
		file_put_contents($file, $data);
		//zwracanie informacji o powodzeniu
		return true;
	}
	//odczytanie pliku
	private function _readFile($db_name){
		//gerowanie ścieżki do pliku bazy
		$file = $this->path.$db_name.'.php';
		//błąd jeżeli plik nie isnieje
		if(!file_exists($file)) return false;
		//wczytanie pliku
		$read = include($file);
		//odczytywanie zakodowanych danych z pliku
		$decode = unserialize($read);
		//błąd jeżeli dane to nie tablica lub odczytane dane są puste
		if(!is_array($decode) or $read == '') return false;
		//zwrócenie tablicy
		return $decode;
		
	}
}