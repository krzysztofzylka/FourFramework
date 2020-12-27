<?php
//łączenie z bazą danych
$this->connect = function(string $dbName, string $dbPassword = null){
	core::setError();
	$dbName = htmlspecialchars(basename($dbName)); //nazwa
	$dbPassword = htmlspecialchars(basename($dbPassword)); //hasło
	$dbPath = $this->basePath.$dbName.'/'; //generowanie ścieżki do bazy
	if(!file_exists($dbPath)) //sprawdzenie czy baza danych nie istnieje
		return core::setError(1, 'Database is not exists');
	$dbPassPath = $dbPath.'passwd.php'; //ścieżka do pliku z hasłem
	if(!file_exists($dbPassPath)) //sprawdzenie czy plik z hasłem istnieje
		return core::setError(2, 'Password file is not exists (passwd.php)');
	$dbPass = include($dbPassPath); //pobranie hasła z pliku
	if(!core::$library->crypt->hashCheck($dbPassword, $dbPass)) //sprawdzenie poprawności hasła
		return core::setError(3, 'Password incorect');
	//sprawdzanie czy połączenie z taką bazą jest już zainicjonowane (jeżeli tak to zwracanie tamtego ID)
	if($this->connection <> null){ //jeżeli jakiekolwiek połączenie istnieje
		$dbConnectionSearch = core::$library->array->searchByKey($this->connection, 'name', $dbName); //wyszukiwanie połączenia z bazą o takiej samej nazwie
		if($dbConnectionSearch <> -1){ //jeżeli znaleziono połączenie
			if($dbPass === $this->connection[$dbConnectionSearch]['pass']){ //jeżeli hasła się zgadzają
				return $dbConnectionSearch; //zwracanie ID wyszukanego elementu
			}
		}
	}
	$uniqueID = $this->__generateUniqueConnectionID(); //generowanie unikalnego ID połączenia
	$this->connection[$uniqueID] = [ //dodanie połączenia do tablicy
		'name' => $dbName, //nazwa bazy
		'path' => $dbPath, //ścieżka do bazy
		'pass' => $dbPass //hasło do bazy
	];
	$this->conn = $uniqueID; //ustalenie ostatniego połączenia
	return $uniqueID; //zwrócenie identyfikatora połączenia
	// var_dump($dbName, $dbPassword, $dbPath, $dbPassPath, $dbPass, $uniqueID, $this->connection[$uniqueID]);
};

//tworzenie nowej bazy danych
$this->createDatabase = function(string $dbName, string $dbPassword = null){
	core::setError();
	$dbName = htmlspecialchars(basename($dbName)); //nazwa
	$dbPassword = htmlspecialchars(basename($dbPassword)); //hasło
	$dbPath = $this->basePath.$dbName.'/'; //generowanie ścieżki do bazy
	if(file_exists($dbPath)) //sprawdzenie czy baza danych istnieje
		return core::setError(1, 'Database is already exists');
	mkdir($dbPath, 0700, true); //tworzenie folderu bazy danych
	file_put_contents($dbPath.'passwd.php', "<?php return '".core::$library->crypt->hash($dbPassword, $this->passwordCryptAlg)."'; ?>"); //tworzenie pliku z hasłem
	return true;
};

//wykonanie zapytania do bazy danych
$this->request = function(string $script, string $connection = null){
	core::setError();
	if(!is_array($this->connection) or count($this->connection) == 0) //jeżeli nie połączono z bazą
		return core::setError(1, 'connection error');
		if($connection === null)
			$connection = array_keys($this->connection)[0]; //jeżeli zmienna $connection zawiera null wybranie pierwszej bazy danych
	$this->__log([$script, $connection]);
	foreach($this->regex as $regexp){ //pętla regexp
		preg_match_all('/'.$regexp.'/msi', $script, $matches, PREG_SET_ORDER, 0);
		if(count($matches) > 0){ //jeżeli znaleziono
			$this->activeConnect = $connection; //ustalenie aktywnego połączenia dla wywołania funkcji
			$this->__log($matches);
			unset($matches[0][0]); //czyszczenie pierwszego elementu regexp (pełny skrypt)
			$matches = array_values($matches[0]); //pobranie wartości z tablicy oraz jej sortowanie
			$request = $this->_request($matches); //wywołanie funkcji _request
			$this->activeConnect = null; //czyszczenie aktywnego połączenia
			return $request; //zwrócenie danych z funkcji _request
		}
	}
	return core::setError(2, 'the script is invalid');
};

//funkcja zwracająca listę baz danych
$this->databaseList = function($option = []){
	core::setError();
	if(!isset($option['version'])) $option['version'] = false; //pobranie wersji bazy danych
	$return = [];
	$path = core::$path['base'].'db/';
	$scan = scandir($path);
	$scan = array_diff($scan, ['.', '..', '.htaccess']);
	foreach($scan as $name){
		$dbVersion = $option['version']===false?null:true;
		$dbPath = $path.$name.'/';
		if(file_exists($dbPath.'passwd.php')){
			$tabele = [];
			$scanTable = scandir($dbPath);
			foreach($scanTable as $id => $tableName){
				if(substr($tableName, strlen($tableName)-3) == 'fdb'){
					if($dbVersion === true){ //jeżeli ma byc pobrana wersja bazy danych
						$FirstTableConfig = json_decode(file($dbPath.$tableName)[0], true); //pobranie konfiguracji tabeli
						$dbVersion = $FirstTableConfig['version'];
					}
					array_push($tabele, substr($tableName, 0, strlen($tableName)-4));
				}
			}
			$return[$name] = [
				'name' => $name,
				'path' => $dbPath,
				'version' => $dbVersion,
				'table' => $tabele
			];
		}
	}
	return $return;
};
?>