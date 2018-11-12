<?php
class account{
	//wczytywanie jądra
	private $core;
	//konfiguracja
	private $config;
	//nazwa sesji z id użytkownika
	public $sessionName = "user_id";
	//baza danych z uzytkownikami
	public $account = Array(
		//tabela z użytkownikami
		'tableName' => 'uzytkownicy',
		//nazwa tabeli z loginami
		'login' => 'login',
		//nazwa tabeli z hasłami
		'password' => 'haslo'
	);
	public $perm = Array(
		//tabela z uprawnieniami
		'tableName' => 'uprawnienia'
	);
	//strona do której użytkownik jest wylogowywany
	public $logoutLink = "index.php";
	//dane użytkownika
	public $user = null;
	//typ kodowania hasła
	public $hash_type = 'md5';
	//funkcja z bazą danych
	private $connect;
	//funkcja informjujaca czy uzytkjownik ma zostać wylogowany po sprawdzeniu funkcją check
	public $logout = true;
	//główna funkcja
	public function __construct($obj, $config){
		//dodanie jądra do zmiennej wewnętrznej
		$this->core = $obj;
		$this->config = $config;
		$this->connect = $this->core->module['database']->connect;
	}
	//pobieranie do zmiennej $this->user danych użytkownika
	public function getUserData(){
		//sprawdzanie czy połączenie z bazą danych jest poprawne
		$this->check_dbmodule();
		//sprawdzenie czy sesja istnieje
		if(isset($_SESSION[$this->sessionName])){
			//pobieranie ID uzytkownika
			$uid = $this->getUserID();
			//tworzenie zapytania
			$prepare = $this->connect->prepare("SELECT * FROM ".$this->account['tableName']." WHERE id=:id");
			//dodawnie do zapytania danych
			$prepare->bindParam(":id", $uid, PDO::PARAM_INT);
			//wykonywanie zapytania
			$prepare->execute();
			//sprawdzanie czy wysyłanie zapytania do bazy danych przebiegło pomyślnie
			$this->core->module['database']->checkError($prepare);
			//odczytanie danych z zapytania
			$this->user = $prepare->fetch(PDO::FETCH_ASSOC);
			return true;
		} else return false;
	}
	//funkcja logowania
	public function login($login, $password, $addional = ""){
		//sprawdzanie czy połączenie z bazą danych jest poprawne
		$this->check_dbmodule();
		//wywoływanie zapytania
		$prepare = $this->connect->prepare("SELECT count(id) as count, id FROM ".$this->account['tableName']." WHERE ".$this->account['login']."=:login and ".$this->account['password']."=:haslo ".$addional." LIMIT 1");
		//login
		$prepare->bindParam(":login", $login, PDO::PARAM_STR);
		//hasło
		$haslo = $this->hashpass($password);
		$prepare->bindParam(":haslo", $haslo, PDO::PARAM_STR);
		//wykonywanie zapytania
		$prepare->execute();
		//sprawdzanie czy wysyłanie zapytania do bazy danych przebiegło pomyślnie
		$this->core->module['database']->checkError($prepare);
		//pobieranie danych z zapytania
		$row = $prepare->fetch(PDO::FETCH_NUM);
		//jeżeli użytkownik istnieje
		if($row[0] == 1){
			//informacja o poprawności zalogowania
			$this->core->log_message('Success login id: '.$row[1].' (module: account)');
			//tworzenie sesji
			$this->createSession($row[1]);
			return true;
			//użytkownik nie istnieje
		}else return false;
	}
	//funkcja wylogowywująca
	public function logout(){
		//informacja o poprawnym wylogowaniu
		$this->core->log_message('Success logout (module: account)');
		//usunięcie sesji
		unset($_SESSION[$this->sessionName]);
		//przeniesienie użytkownika
		header('location: '.$this->logoutLink);
	}
	//sprawdzanie czy użytkownik jest zalogowny, jeżeli nie (ktoś próbuje się dostać tam gdzie nie powinien) wylogowywuje
	public function check(){
		//sprawdzenie czy sesja istnieje
		if(isset($_SESSION[$this->sessionName]) and is_int($_SESSION[$this->sessionName]) and $_SESSION[$this->sessionName] > 0){
			$this->getUserData();
			return true;
		//wylogowywanie jeżeli nie istnieje
		}else{
			if($this->logout == true) $this->logout();
			return false;
		}
	}
	//tworzenie sesji
	private function createSession($id){
		$this->core->log_message('Success create session id:'.$id.' (module: account)');
		//tworzenie sesji
		$_SESSION[$this->sessionName] = (int)$id;
	}
	//funkcja zmieniająca hasło
	public function changePassword($password){
		//sprawdzanie czy połączenie z bazą danych jest poprawne
		$this->check_dbmodule();
		//sprawdzenie czy użytkownik istnieje
		if($this->check() == true){
			//pobieranie ID użytkownika
			$int = $this->getUserID();
			//wywoływanie zapytania
			$prepare = $this->connect->prepare("UPDATE ".$this->account['tableName']." SET ".$this->account['password']."=:haslo WHERE id=:id");
			//hasło
			$password = $this->hashpass($password); //kodowanie
			//podmiana danych
			$prepare->bindParam(":haslo", $password, PDO::PARAM_STR);
			$prepare->bindParam(":id", $int, PDO::PARAM_INT);
			//wykonywanie hasła
			$prepare->execute();
			//sprawdzanie czy wysyłanie zapytania do bazy danych przebiegło pomyślnie
			$this->core->module['database']->checkError($prepare);
			return true;
		}
	}
	//wyświetlenie formularza logowania
	public function loginForm($config=null){
		$path = $this->config['path'];
		include($path."/form/login.php");
	}
	//funkcja kodująca hasło
	public function hashpass($password){
		//typ kodowania hasła
		switch($this->hash_type){
			//brak kodowania
			case null:
				return $password;
				break;
			//kodowanie md5
			case 'md5':
				//kodowanie
				return md5($password);
				break;
		}
	}
	//sprawdzanie czy uzytkownik ma dostęp do strony
	public function permission_check($name){
		//sprawdzanie czy połączenie z bazą danych jest poprawne
		$this->check_dbmodule();
		//pobranie ID użytkownika
		$user_id = $this->getUserID();
		//tworzenie zapytania
		$prepare = $this->connect->prepare("SELECT count(*) as count, pozwolenie FROM ".$this->perm['tableName']." WHERE uprawnienie=:nazwa AND uzytkownik=:uid")->fetch(PDO::FETCH_ASSOC);
		//id uzytkownika
		$prepare->bindParam(":uid", $user_id, PDO::PARAM_INT);
		//nazwa uprawnienia
		$prepare->bindParam(":nazwa", $name, PDO::PARAM_STR);
		//wykonanie zapytania
		$prepare->execute();
		//sprawdzanie czy wysyłanie zapytania do bazy danych przebiegło pomyślnie
		$this->core->module['database']->checkError($prepare);
		//pobranie danych z zapytania
		$row = $prepare->fetch(PDO::FETCH_NUM);
		//sprawdzanie czy uzytkownik ma uprawnienie do przeglądania strony
		if($row[0] >= 1 and $row[1] == 1) return true;
		//jeżeli nie to zwracanie informacji o błędzie
		return false;
	}
	//pobieranie ID użytkownika
	public function getUserID(){
		return (int)$_SESSION[$this->sessionName];
	}
	//funkcja sprawdzająca poprawność połączenia oraz modułu bazy danych
	private function check_dbmodule(){
		//sprawdzanie czy połączenie z bazą danych jest poprawne
		if(!$this->connect instanceof PDO){
			//próba naprawy połączenia
			$this->connect = $this->core->module['database']->connect;
			//jeżeli jest dalej błąd to wyświetlenie błędu krytycznego
			if(!$this->connect instanceof PDO) $this->core->_fatalError('Error module database (module account)');
		}
	}
	//wyświetlenie formularza rejestracji
	public function registerForm($config=null){
		$path = $this->config['path'];
		include($path."/form/rejestracja.php");
	}
	//funkcja rejestrująca użytkownika
	public function register($login, $password){
		//sprawdzanie czy połączenie z bazą danych jest poprawne
		$this->check_dbmodule();
		//wywoływanie zapytania
		$prepare = $this->connect->prepare("INSERT INTO ".$this->account['tableName']."(login, haslo) VALUES (:login, :haslo)");
		//login
		$prepare->bindParam(":login", $login, PDO::PARAM_STR);
		//hasło
		$haslo = $this->hashpass($password);
		$prepare->bindParam(":haslo", $haslo, PDO::PARAM_STR);
		//wykonywanie zapytania
		$prepare->execute();
		//sprawdzanie czy wysyłanie zapytania do bazy danych przebiegło pomyślnie
		$this->core->module['database']->checkError($prepare);
		//jeżeli użytkownik istnieje
		if($row[0] == 1){
			//informacja o poprawności zalogowania
			$this->core->log_message('Success register account (module: account)');
			return true;
			//użytkownik nie istnieje
		}else return false;
	}
	//funkcja debugująca
	public function __debugInfo() {
		$user_data = '***';
		if(is_array($this->user)){
			$user_data = $this->user;
			$user_data[$this->account['password']] = '*** hidden ***';
		}
        return [
			'version' => $this->config['version'],
			'sessionName' => $this->sessionName,
			'logoutLink' => $this->logoutLink,
			'requireModule' => $this->config['module_include'],
			'user' => $user_data,
			'getUserID' => isset($_SESSION[$this->sessionName])==false?'***':$this->getUserID(),
			'hash_type' => $this->hash_type,
			'function' => array('getUserData', 'login', 'logout', 'check', 'changePassword', 'loginForm', 'permission_check', 'getUserID', 'registerForm', 'register', 'hashpass'),
		];
    }
}
?>