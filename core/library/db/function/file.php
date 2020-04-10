<?php
//Zapisz pliku fdb do bazy danych
$this->____saveDBFile = function(string $tableName, array $tableData, array $option = []){
	core::setError();
	if($this->saveDBFile === false) return false; //jeżeli zapis do bazy danych wyłączony
	//reindeksowanie tabeli
	$tableData['column'] = array_values($tableData['column']); //kolumny
	$tableData['data'] = array_values($tableData['data']); //dane
	//konfiguracja domyślnych dodatkowych opcji
	if(!isset($option['crypt'])) $option['crypt'] = true; //szyfrowanie pliku bazy danych
	$password = $this->connection[$this->activeConnect]['pass'];
	//zmiana kolumn tablicy na json oraz szyfrowanie (jeżeli włączone)
	if($option['crypt'] === true) $tableData['option']['autoincrement'] = core::$library->crypt->crypt(json_encode($tableData['option']['autoincrement']), $password); //szyfrowanie tablicy z autoincrement
	$tableData['option'] = json_encode($tableData['option']); //tablica z opcjami
	if($option['crypt'] === true) $tableData['column'] = core::$library->crypt->crypt(json_encode($tableData['column']), $password); //szyfrowanie tablicy z kolumnami
	else $tableData['column'] = json_encode($tableData['column']); //tablica z kolumnami
	if($option['crypt'] === true) $tableData['data'] = core::$library->crypt->crypt(json_encode($tableData['data']), $password); //szyfrowanie tablicy z danymi
	else $tableData['data'] = json_encode($tableData['data']); //tablica z danymi
	$tableText = $tableData['option'].PHP_EOL //utworzenie danych dla pliku .FDB
	.$tableData['column'].PHP_EOL
	.$tableData['data'];
	file_put_contents($this->connection[$this->activeConnect]['path'].$tableName.'.fdb', $tableText);
	return true;
	$this->__log(['$tableData' => $tableData, '$password' => $password, '$option' => $option, '$tableText' => $tableText]);
};

$this->____readDBFile = function(string $tableName, string $readType='all', array $option = []){
	core::setError();
	if(!isset($option['returnJSON'])) $option['returnJSON'] = false; //czy dane mają być zwrócone w formie JSON
	$readFile = file($this->connection[$this->activeConnect]['path'].$tableName.'.fdb'); //wczytanie pliku bazy danych
	$password = $this->connection[$this->activeConnect]['pass']; //pobranie hasła
	$returnData = ['option' => null, 'column' => null, 'data' => null]; //domyślne zwracane dane
	switch($readType){
		case "all": //jeżeli zwrócone mają być wszystkie
		case "option": //tylko opcje
			$returnData['option'] = json_decode(trim($readFile[0]), true); //odczyt
			$returnData['option']['autoincrement'] = json_decode(core::$library->crypt->decrypt(trim($returnData['option']['autoincrement']), $password), true); //odczytanie i deszyfrowanie licznika
			if($option['returnJSON'] === true) $returnData['option'] = json_encode($returnData['option']);
			if($readType === "option") return $returnData['option']; //tylko opcje
		case "column": //tylko kolumny
			$returnData['column'] = json_decode(core::$library->crypt->decrypt(trim($readFile[1]), $password), true); //odczytanie i deszyfrowanie
			if(!is_array($returnData['column'])) $returnData['column'] = []; //naprawa
			if($option['returnJSON'] === true) $returnData['column'] = json_encode($returnData['column']);
			if($readType === "column") return $returnData['column']; //tylko kolumny
		case "data": //tylko dane
			$returnData['data'] = json_decode(core::$library->crypt->decrypt(trim($readFile[2]), $password), true); //odczytanie i deszyfrowanie
			if(!is_array($returnData['data'])) $returnData['data'] = []; //naprawa
			if($option['returnJSON'] === true) $returnData['data'] = json_encode($returnData['data']);
			if($readType === "data") return $returnData['data']; //tylko dane
	}
	return $returnData; //zwrócenie tablicy z odczytanymi danymi (jeżeli pobrane miało być wszystko)
};
?>