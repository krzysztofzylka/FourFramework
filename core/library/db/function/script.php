<?php
//wykonywanie skryptu z funkcji request
$this->_request = function(array $data){
	core::setError();
	$this->__log(['$data' => $data]);
	switch(strtoupper($data[0])){ //zmiana ciągu na duże litery
		//utworzenie tabeli
		case 'CREATE TABLE':
			$tableName = $this->__clearString($data[1]); //pobranie nazwy tabeli
			$tableColumn = core::$library->string->explode(',', $data[2]); //rozdzielenie tabel
			$tableColumn = core::$library->array->trim($tableColumn); //czyszczenie tablicy ze zbędnych znaków
			foreach($tableColumn as $id => $item){ //pętla z danymi tabeli
				$tableColumn[$id] = $this->___columnDataExplode($item); //rozdzielenie ciągu tabeli na tablice
				if(core::$error[0] > -1) //jeżeli błąd z funkcji ___columnDataExplode
					return core::setError(102, 'error add column', 'error column type');
			}
			return $this->_createTable($tableName, $tableColumn); //wywołanie funkcji tworzącej tabele
			// $this->__log(['$data' => $data, '$tableName' => $tableName, '$tableColumn' => $tableColumn]);
			break;
		//dodanie danych do tabeli
		case 'ADD DATA TO':
			$tableName = $this->__clearString($data[1]); //pobranie nazwy tabeli
			//pobranie kolumn i wartości dla nich
			$dataColumn = core::$library->string->explode(',', $data[2]); //pobranie nagłówki kolumn
			foreach($dataColumn as $id=>$text) $dataColumn[$id] = $this->__clearString($text); //czyszczenie ciągu ze zbędnych znaków
			$dataValues = core::$library->string->explode(',', $data[3]); //pobranie wartości dla kolumn
			foreach($dataValues as $id=>$text) $dataValues[$id] = $this->__clearString($text); //czyszczenie ciągu ze zbędnych znaków
			$this->__log(['$dataColumn' => $dataColumn, '$dataValues'=> $dataValues]);
			if(count($dataColumn) <> count($dataValues)) //jeżeli liczba kolumn nie jest taka sama jak liczba danych
				return core::setError(50, 'Data count error');
			//łączenie danych i wartości w jedną tablicę
			$dataCombine = array_combine($dataColumn, $dataValues);
			return $this->_addDataTo($tableName, $dataCombine);
			// $this->__log(['$data' => $data, '$tableName' => $tableName, '$dataColumn' => $dataColumn, '$dataValues' => $dataValues, '$dataCombine' => $dataCombine]);
			break;
		//pobranie danych z tabeli
		case 'SELECT':
			$tableName = $this->__clearString($data[2]); //pobranie nazwy tabeli
			$selectColumn = core::$library->string->explode(',', $data[1]); //pobranie listy kolumn do wyświetlenia
			foreach($selectColumn as $id => $text) //czyszczenie listy kolumn ze zbędnych znaków
				$selectColumn[$id] = $this->__clearString($text);
			if($selectColumn === [''] or $selectColumn === ['*']) $selectColumn = []; //zabezpieczenie aby nie przeszło puste lub ze znakiem * (całość)
			$whereData = isset($data[3])?core::$library->string->explode('and', $data[3]):[]; //pobranie danych dla where lub [] jeżeli nie ma skryptu where
			$this->__log(['$data' => $data, '$tableName' => $tableName, '$selectColumn' => $selectColumn, '$whereData' => $whereData]);
			return $this->_selectData($tableName, $whereData, $selectColumn);
			break;
		//usuwanie danych
		case 'DELETE':
			$tableName = $this->__clearString($data[1]); //pobranie nazwy tabeli
			$whereData = isset($data[2])?core::$library->string->explode('and', $data[2]):[]; //pobranie danych dla where lub [] jeżeli nie ma skryptu where
			return $this->_deleteData($tableName, $whereData); //wywołanie funkcji usuwającej dane
			// $this->__log(['$data' => $data, '$tableName' => $tableName, '$whereData' => $whereData]);
			break;
		//aktualizowanie danych
		case 'UPDATE':
			$tableName = $this->__clearString($data[1]); //pobranie nazwy tabeli
			//pobranie danych do podmiany
			$setData = core::$library->string->explode(',', $data[2]);
			foreach($setData as $idData => $valueData){
				$setData[$idData] = core::$library->string->explode('=', $valueData); //rozdzielenie danych
				$setData[$idData][0] = $this->__clearString($setData[$idData][0]); //czyszczenie nazwy kolumny
				$setData[$idData][1] = $this->__clearString($setData[$idData][1]); //czyszczenie nowej treści
			}
			$whereData = isset($data[3])?core::$library->string->explode('and', $data[3]):[]; //pobranie danych dla where lub [] jeżeli nie ma skryptu where
			return $this->_updateData($tableName, $setData, $whereData); //wysłanie danych do funkcji aktualizującej
			// $this->__log(['$tableName' => $tableName, '$setData' => $setData, '$whereData' => $whereData]);
			break;
		//opcje zaawansowane
		case 'ADVENCED':
			$tableName = null; //domyślnie brak nazwy tabeli
			if(isset($data[3])){ //jeżeli istnieje opcja FROM
				$tableName = $this->__clearString($data[3]); //pobranie nazwy tabeli
				if(!$this->__checkTable($tableName)) return false; //jeżeli tabela nie istnieje to błąd
			}
			return $this->_advenced(strtoupper($data[1]), $data[2], $tableName); //wywołanie funkcji advenced
			break;
	}
};

//dodanie danych do tabeli
$this->_addDataTo = function(string $tableName, array $data){
	core::setError();
	if(!$this->__checkTable($tableName)) return false; //jeżeli tabela nie istnieje
	$tableRead = $this->____readDBFile($tableName); //odczytanie danych z tabeli
	$newData = []; //tablica dla nowych danych (dla tablicy $data)
	foreach($tableRead['column'] as $column){ //pętla dla kolumn (z tablicy)
		// core::$library->debug->print_r($column);
		if(isset($data[$column['name']])){ //sprawdzanie poprawności
			//jeżeli znaleziono dane dla kolumny w zmiennej $data
			$columnData = $this->___CheckingDataForAColumn($data[$column['name']], $column); //sprawdzenie oraz skorygowanie danych wg. potrzeby
			if(core::$error[0] > -1) return false; //anulowanie funkcji jeżeli błąd
			$newData[$column['name']] = $columnData; //dodanie danych do tablicy $newData
		}else{
			//jeżeli nie znaleziono danych dla kolumny w zmiennej $data
			//jeżeli kolumna posiada dane w defaultData (są inne niż null)
			if($column['defaultData'] <> null){
				$columnData = $this->___CheckingDataForAColumn($column['defaultData'], $column); //sprawdzenie oraz skorygowanie danych wg. potrzeby
				if(core::$error[0] > -1) return false; //anulowanie funkcji jeżeli błąd
				$newData[$column['name']] = $columnData; //przypisanie domyślnych danych do kolumny
			//jeżeli kolumna to autoodliczanie
			}elseif($column['autoincrement'] === true){
				$newData[$column['name']] = (int)$tableRead['option']['autoincrement']['count']; //przypisanie danych z autoincrement
				$tableRead['option']['autoincrement']['count']++; //dodanie licznika autoincrement +1
			}else //jeżeli żadne z powyższych wyświetla błąd kolumny
				return core::setError(51, 'Column not found: '.$column['name']); //błąd
		}
	}
	$tableRead['option']['dataCount']++; //dodawanie licznika danych +1
	array_push($tableRead['data'], $newData); //dodawanie danych do tabeli
	$this->____saveDBFile($tableName, $tableRead); //zapis danych do pliku
	return true;
	$this->__log(['$tableName' => $tableName, '$data' => $data, '$tableRead' => $tableRead]);
};

//funkcja tworząca tabelę
$this->_createTable = function(string $tableName, array $tableColumn){
	core::setError();
	if(file_exists($this->connection[$this->activeConnect]['path'].$tableName.'.fdb')) //błąd jeżeli tabela o takiej nazwie już istnieje w bazie
		return core::setError(101, 'error create table', 'table is already exists');
	$tableArray = [ //główna tablica (zawiera wszystkie dane tabeli
		'option' => [ //opcje kolumny, nie szyfrowane
			'name' => $tableName, //nazwa tabeli
			'version' => $this->tableVersion, //wersja tabeli
			'columnCount' => count($tableColumn), //ilość kolumny
			'dataCount' => 0, //ilość danych w tabeli
			'autoincrement' => [ //autoodliczanie (szyfrowane)
				'ai' => false, //czy autoodliczanie aktywne
				'colName' => null, //nazwa kolumny do autoodliczania
				'count' => 1 //licznik autoodliczania
			]
		],
		'column' => $tableColumn, //kolumny
		'data' => [] //tablica na wszystkie dane w tabeli
	];
	//autoincrement
	$aiSearch = core::$library->array->searchByKey($tableColumn, 'autoincrement', 1); //wyszukiwanie czy istnieje tabela autoincrement
	if($aiSearch > -1){ //jeżeli znaleziono
		$tableArray['option']['autoincrement']['ai'] = true; //aktywacja AI
		$tableArray['option']['autoincrement']['colName'] = $tableColumn[$aiSearch]['name']; //ustalenie nazwy kolumny
	}
	return $this->____saveDBFile($tableName, $tableArray); //zapis danych do pliku
	// $this->__log(['$tableArray' => $tableArray]);
};

//wybranie i wyświetlenie elementów z bazy danych
$this->_selectData = function(string $tableName, array $whereData = [], array $selectColumn = []){
	core::setError();
	if(!$this->__checkTable($tableName)) return false; //jeżeli tabela nie istnieje
	$returnData = $this->____readDBFile($tableName, 'data'); //odczytanie danych z tabeli
	if(count($returnData) === 0) //jeżeli brak danych w tabeli
		return $returnData; //zwracanie pustej tablicy
	//wyszukiwanie danych
	if($whereData <> []){ //pominięcie jeżeli brak wyszukiwania
		$returnData = $this->___whereData($returnData, $whereData);
		if(core::$error[0] > -1) return false; //jeżeli błąd funkcji where
	}
	//usunięcie ze zwracanych danych, tych kolumn których użytkownik nie chce widzieć
	if($selectColumn <> []) //pominięcie w przypadku wyświetlenia wszystkich
		foreach($returnData as $dataID => $dataArray)
			$returnData[$dataID] = array_intersect_key($dataArray, array_fill_keys($selectColumn, ''));
	return $returnData; //zwrócenie danych
	$this->__log(['$whereData' => $whereData, '$selectColumn' => $selectColumn, '$returnData' => $returnData, '$tableRead' => $tableRead]);
};

//usuwanie danych z tabeli
$this->_deleteData = function(string $tableName, array $whereData = []){
	core::setError();
	if(!$this->__checkTable($tableName)) return false; //jeżeli tabela nie istnieje
	$tableRead = $this->____readDBFile($tableName); //odczytanie danych z tabeli
	// wyszukiwanie danych
	if($whereData <> []){ //pominięcie jeżeli brak wyszukiwania
		$dataSearchKey = array_keys($this->___whereData($tableRead['data'], $whereData, ['arrayValues' => false])); //jeżeli usunięte mają być tylko wyszukane elementy
		if(core::$error[0] > -1) return false; //jeżeli błąd funkcji where
		if(count($dataSearchKey) == $tableRead['option']['dataCount']) //jeżeli wybrane są wszystkie dane
			$tableRead['data'] = []; //usuwanie wszystkich danych z tablicy data
		else
			$tableRead['data'] = array_diff_key($tableRead['data'], array_fill_keys($dataSearchKey, '')); //usuwanie tylko wyszukanych elementów
	}else //jeżeli usunięte mają być wszystkie elementy
		$tableRead['data'] = []; //usuwanie wszystkich danych z tablicy data
	$deleteCount = $tableRead['option']['dataCount']-count($tableRead['data']); //ilość usuniętych danych
	$tableRead['option']['dataCount'] = count($tableRead['data']); //aktualizowanie ilości danych w tablicy
	$this->____saveDBFile($tableName, $tableRead); //aktualizowanie pliku tabeli
	return $deleteCount; //zwrócenie ilości usuniętych danych
	$this->__log(['$tableName' => $tableName, '$whereData' => $whereData, '$dataSearchKey' => $dataSearchKey, '$tableRead' => $tableRead]);
};

//aktualowanie danych w tabeli
$this->_updateData = function(string $tableName, array $setData, array $whereData = []){
	core::setError();
	if(!$this->__checkTable($tableName)) return false; //jeżeli tabela nie istnieje
	$tableRead = $this->____readDBFile($tableName); //odczytanie danych z tabeli
	//wyszukanie danych w tabeli
	if($whereData <> []) //pominięcie jeżeli brak wyszukiwania
		$dataSearchKey = array_keys($this->___whereData($tableRead['data'], $whereData, ['arrayValues' => false])); //pobranie kluczy wyszukiwanych elementów
	else
		$dataSearchKey = array_keys($tableRead['data']); //pobranie kluczy wszystkich elementów
	//sprawdzenie czy kolumny z $setData istnieją w bazie
	foreach($setData as $setArray){
		$setColumn = $setArray[0]; //pobranie nazwy kolumny
		if(core::$library->array->searchByKey($tableRead['column'], 'name', $setColumn) === -1) //jeżeli kolumna nie istnieje w tabeli
			return core::setError(21, 'column not found', 'name: '.$setColumn);
	}
	//aktualizacja danych
	foreach($dataSearchKey as $dataKey){ //pętla po wyszukanych kluczach
		$findDataArray = $tableRead['data'][$dataKey]; //pobranie danych do tablicy
		foreach($setData as $setArray){ //pętla po danych do aktualizacji
			$setColumn = $setArray[0]; //pobranie nazwy kolumny
			$setValues = $setArray[1]; //pobranie nowych danych dla kolumny
			$columnKey = core::$library->array->searchByKey($tableRead['column'], 'name', $setColumn); //id kolumny z tabeli
			$columnArray = $tableRead['column'][$columnKey]; //pobranie kolumny z tabeli do zmiennej
			$setValues = $this->___CheckingDataForAColumn($setValues, $columnArray); //poprawa wartości danych dla kolumny oraz sprawdzenie poprawności
			if(core::$error[0] > -1)
				return false;
			$findDataArray[$setColumn] = $setValues; //aktualizacja danych
		}
		$tableRead['data'][$dataKey] = $findDataArray; //zaktualiwanie danych
	}
	$this->____saveDBFile($tableName, $tableRead); //aktualizowanie pliku tabeli
	return count($dataSearchKey); //zwrócenie ilości zaktualizowanych danych
	// $this->__log(['$tableName' => $tableName, '$setData' => $setData, '$whereData' => $whereData, '$dataSearchKey' => $dataSearchKey, '$tableRead' => $tableRead]);
};

//zaawansowane opcje tabeli
//$opt - opcja (GET/SET)
//$data - dane do wywołania
//$tableName - nazwa tabeli
$this->_advenced = function(string $opt, string $data, string $tableName = null){
	core::setError();
	switch($opt){ //opcja GET/SET
		case 'GET': //jeżeli pobranie danych
			switch($data){ //dane do pobrania
				case 'tableList': //lista tabel w bazie
					$scanDBPath = glob($this->connection[$this->activeConnect]['path'].'*.{fdb}', GLOB_BRACE); //pobranie wszystkich plików z bazy o rozszerzeniu fdb
					foreach($scanDBPath as $scanDBPathID => $dirPath) //petla po plikach
						$scanDBPath[$scanDBPathID] = str_replace('.fdb', '', basename($dirPath)); //usunięcie zbędnych znaków i zostawienie samej nazwy
					return $scanDBPath; //zwrócenie listy tabel
					break;
				case 'column': //lista kolumn i ich właściwości
					if($tableName === null) return core::setError(2, 'the script is invalid'); //błąd jeżeli nie wybrano tabeli
					$columnList = $this->____readDBFile($tableName, 'column'); //pobranie listy kolumn
					$columnList['count'] = count($columnList); //pobranie ilości kolumn
					return $columnList; //zwrócenie tablicy column z pliku bazy danych
					break;
				case 'autoincrement': //zwrócenie danych autoodliczania
					if($tableName === null) return core::setError(2, 'the script is invalid'); //błąd jeżeli nie wybrano tabeli
					return $this->____readDBFile($tableName, 'option')['autoincrement'];
					break;
				default: //jeżeli błąd i nie znalezionych obsługiwanych danych
					return core::setError(2, 'the script is invalid');
			}
			break;
		case 'SET': //jeżeli zapisanie danych
			return core::setError(2, 'the script is invalid'); //błąd z powodu braku obsługi SET (tymczasowy)
			break;
		default: //jeżeli niepoprawny kod to błąd
			return core::setError(2, 'the script is invalid');
			break;
	}
	$this->__log(['$opt' => $opt, '$data' => $data, '$tableName' => $tableName]);
};
?>