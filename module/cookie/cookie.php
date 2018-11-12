<?php
//moduł operujący na ciasteczkach
class cookie{
	public $hash_name = false;
	private $core;
	private $config;
	private $hash;
	private $method = 'AES-256-CBC';
	private $iv;
	//funkcja główna
	public function __construct($core, $config){
		//inicjonowanie zmiennych
		$this->core = $core;
		$this->config = $config;
		//ścieżka do pliku w hashem
		$path = $config['path'].'hash.php';
		//jezeli hash istnieje
		if(file_exists($path)){
			//wczytywanie hasła
			$this->hash = include($path);
		//jeżeli hash nie istnieje
		}else{
			//generowanie hasła
			$generate = md5(rand(100000000000000, 999999999999999));
			//zapis hasła do pliku
			$file = fopen($path, "x");
			fwrite($file, "<?php return '".$generate."'; ?>");
			fclose($file);
			//wczytywanie hasła
			$this->hash = $generate;
		}
		
		//ścieżka do pliku w iv
		$path = $config['path'].'iv.php';
		//jezeli iv istnieje
		if(file_exists($path)){
			//wczytywanie hasła
			$this->iv = include($path);
		//jeżeli hash nie istnieje
		}else{
			//generowanie hasła
			$generate = substr(hash('sha256', rand(100000000000000, 999999999999999)), 0, 16);
			//zapis hasła do pliku
			$file = fopen($path, "x");
			fwrite($file, "<?php return '".$generate."'; ?>");
			fclose($file);
			//wczytywanie hasła
			$this->iv = $generate;
		}
	}
	//dodanie nowego ciasteczka
	public function set($name, $value, $time=86400){ //sekund
		//kodowanie nazwy
		if($this->hash_name == true) $name = $this->_crypt($name);
		//kodowanie tresci
		$value = $this->_crypt($value);
		//jeżeli ciasteczko istnieje to aktualizacja istniejącego
		if($this->check($name)) setcookie($name, $value);
		//ustalenie ciasteczka
		else setcookie($name, $value, time() + ($time), "/");
		//informacja o powodzeniu
		return true;
	}
	//usunięcie ciasteczka
	public function del($name){
		//kodowanie nazwy
		if($this->hash_name == true) $name = $this->_crypt($name);
		//sprawdzanie czy ciasteczko istnieje
		if($this->check($name)){
			//konfiguracja ciasteczka
			setcookie($name, null, -1, '/');
			//usunięcie ciasteczka
			unset($_COOKIE[$name]);
			//zwrócenie informacji o powodzeniu
			return true;
		}else return false;
	}
	//odczytanie ciasteczka
	public function read($name){
		//sprawdzanie czy ciasteczko istnieje
		if(!$this->check($name)) return null;
		//kodowanie nazwy
		if($this->hash_name == true) $name = $this->_crypt($name);
		//jeżeli istnieje zwrócenie wartości ciasteczka
		else return $this->_decode($_COOKIE[$name]);
	}
	//sprawdzanie czy ciasteczko istnieje
	public function check($name){
		//kodowanie nazwy
		if($this->hash_name == true) $name = $this->_crypt($name);
		//zwrócenie wartości bool czy ciastko istnieje
		return isset($_COOKIE[$name]);
	}
	//funkcja kodująca ciasteczko
	private function _crypt($string){
		return base64_encode(openssl_encrypt($string, $this->method, $this->hash, 0, $this->iv));
	}
	//funkcja dekodująca ciasteczko
	private function _decode($string){
		return openssl_decrypt(base64_decode($string), $this->method, $this->hash, 0, $this->iv);
	}
	//funkcja debugująca
	public function __debugInfo() {
		return [
			'version' => $this->config['version'],
			'hash_name' => $this->hash_name==true?'true':'false',
			'hash' => $this->hash,
			'iv' => $this->iv,
			'function' => [
				'set', 'del', 'read', 'check'
			],
			'test' => [
				'string' => 'test',
				'crypt' => $this->_crypt('test'),
				'decrypt' => $this->_decode($this->_crypt('test'))
			]
		];
	}
}