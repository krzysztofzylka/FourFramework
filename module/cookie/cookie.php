<?php
//moduł operujący na ciasteczkach
class cookie{
	//dodanie nowego ciasteczka
	public function set($name, $value, $time=86400){ //sekund
		//jeżeli ciasteczko istnieje to aktualizacja istniejącego
		if($this->check($name)) setcookie($name, $value);
		//ustalenie ciasteczka
		else setcookie($name, $value, time() + ($time), "/");
		//informacja o powodzeniu
		return true;
	}
	//usunięcie ciasteczka
	//zwraca dane bool
	public function del($name){
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
	//jeżeli brak zwraca null
	public function read($name){
		//sprawdzanie czy ciasteczko istnieje
		if(!$this->check($name)) return null;
		//jeżeli istnieje zwrócenie wartości ciasteczka
		else return $_COOKIE[$name];
	}
	//sprawdzanie czy ciasteczko istnieje
	public function check($name){
		//zwrócenie wartości bool czy ciastko istnieje
		return isset($_COOKIE[$name]);
	}
}