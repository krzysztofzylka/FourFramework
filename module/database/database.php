<?php
//moduł operujący na bazie danych
class database{
	//połączenie z bazą danych
	public $connect;
	//host do połączenia
	public $host;
	//nazwa do połączenia
	public $name;
	//login do połączenia
	public $login;
	//hasło do połączenia
	public $password;
	//ścieżka do folderu baz danych
	public $path = null;
	//folder pliku dla SQLite
	public $sqlite;
	//kodowanie znaków
	public $charset = "utf8";
	//typ połączenia mysql/sqlite/postgresql/oracle
	public $type = "mysql";
	//zmienna dla core
	private $core;
	//zmienna dla konfiguracji
	private $config;
	//zmienna z numerem portu
	public $port;
	//konfiguracja modułu
	private $db_config;
	//funkcja główna
	public function __construct($obj, $config){
		$this->core = $obj;
		$this->path = $config['path'];
		$this->db_config = $config;
	}
	//łączenie z bazą danych
	public function connect(){
		$this->core->log_message('Connect type '.$this->type.' (module: database)');
		try{
			switch($this->type){
				//jeżeli mysql
				case 'mysql':
					//łączenie z bazą danych
					$this->connect = new PDO("mysql:host=".$this->host.";dbname=".$this->name, $this->login, $this->password);
					//informacja o sukcesie
					$this->core->log_message('Success connection to MySQL database (module: database)');
					//ustalenie kodowania
					$this->connect->exec("SET CHARACTER SET ".$this->charset);
					return true;
					break;
				//jeżeli sqlite
				case 'sqlite':
					//łączenie z bazą danych
					$this->connect = new PDO("sqlite:".$this->sqlite);
					//informacja o sukcesie
					$this->core->log_message('Success connection to SQLite database (module: database)');
					//ustalenie kodowania
					$this->connect->exec("SET CHARACTER SET ".$this->charset);
					return true;
					break;
				//jezeli PostgreSQL
				case 'postgresql':
					//łączenie z bazą danych
					$this->connect = new PDO("pgsql:host=".$this->host.";port=".$this->port.";dbname=".$this->name.";user=".$this->login.";password=".$this->password);
					//informacja o sukcesie
					$this->core->log_message('Success connection to PostgreSQL database (module: database)');
					return true;
					break;
				//jeżeli Oracle
				case 'oracle':
					//łączenie z bazą danych
					$this->connect = new PDO("oci:dbname=".$this->name,$this->login,$this->password);
					//informacja o sukcesie
					$this->core->log_message('Success connection to Oracle database (module: database)');
					break;
			}
		}catch(PDOException $error){
			//błąd fatalny
			$this->core->_fatalError('Error DB Connection: '.$error->getMessage().' (module: database)');
		}
	}
	//ładowanie konfiguracji
	public function loadConfig($file="database_config.php", $dir=-1){
		//jeżeli dane domyślne
		if($dir == -1) $dir = $this->path;
		//ustalenie ścieżki
		$path = $dir.$file;
		//sprawdzenie czy plik istnieje
		if(!file_exists($path)) $this->core->_fatalError('Error loading configuration file, path: '.$path.' (module: database)');
		else{
			//wczytywanie konfiguracji
			$this->config = include($path);
			$this->host = $this->config['host'];
			$this->name = $this->config['name'];
			$this->login = $this->config['login'];
			$this->password = $this->config['password'];
			$this->type = $this->config['type'];
			$this->sqlite = $this->config['sqlitepath'];
			$this->port = $this->config['port'];
			//informacja o sukcesie
			return true;
		}
		//informacja o błędzie
		return false;
	}
	//pobieranie ID ostatniego dodanego elementu
	public function lastInsertID(){
		//zwrócenie ostatnio dodanego ID elementu
		return $this->connect->lastInsertId();
	}
	//wyświetla informacje jeżeli funkcja znalazła w zapytaniu błąd
	public function checkError($var, $log=true){
		if(!$var) {
			//pobranie informacji o błędzie
			$error = $this->connect->errorInfo();
			//wyświetlenie błędu
			echo "\nPDO Error: <b>".$error[2]."</b>\n";
			//wpisanie błędu do logów
			if($log == true) $this->core->log_error("SQL Error: ".$error[2]);
		}
	}
	//funkcja debugująca
	public function __debugInfo() {
        return [
			'version' => $this->db_config['version'],
			'is_connect' => is_object($this->connect),
			'connect' => array(
				'type' => $this->type,
				'host' => $this->host,
				'name' => $this->name,
				'login' => $this->login,
				'password' => $this->password == '' ? '' : '***hidden***',
				'sqlite' => $this->sqlite,
				'port' => $this->port
			),
			'config' => array(
				'charset' => $this->charset
			),
			'function' => array(
				'connect', 'loadConfig', 'lastInsertID', 'checkError'
			)
		];
    }
}
?>