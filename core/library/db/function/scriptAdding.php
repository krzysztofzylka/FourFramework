<?php
//rozdzielenie danych z nazwy kolumn (CREATE TABLE) na uniwersalną tablicę
$this->___columnDataExplode = function(string $columnText){
	core::setError();
	$return = [
		'name' => null, //nazwa kolumny
		'type' => null, //typ danych dla kolumny
		'length' => null, //maksymalna ilość znaków w kolumnie
		'autoincrement' => false, //autoodliczanie kolumny
		'defaultData' => null //domyślne dane dla kolumny
	];
	$columnExpl = core::$library->string->explode(' ', $columnText); //rozdzielenie danych
	$return['name'] = $this->__clearString($columnExpl[0]); //pobranie nazwy kolumny
	//pobranie typu danych oraz jej długości
	$return['length'] = core::$library->string->between($columnExpl[1], '(', ')', 0); //pobranie ilośći znaków dla kolumny
	$return['type'] = $return['length']===null?$columnExpl[1]:str_replace('('.$return['length'].')', '', $columnExpl[1]); //pobranie typu kolumny (jeżeli jest zdefiniowana ilość znaków to usunięcie jej)
	if($return['type'] === 'boolean') $return['length'] = 1; //jeżeli typ boolean ustawienie długości na 1
	if($return['type'] === 'text') $return['length'] = 0; //jeżęli typ text zmana długości na 0 (bez limitu)
	//sprawdzanie poprawności typu
	$search = array_search($return['type'], $this->acceptType);
	if($search === false) //jeżeli nie zlaeziono zwrcóenie błędu
		return core::setError(1, 'Error column data type', 'type: '.$return['type']);
	//wyszukiwanie informacji w zawartych danych
	foreach($columnExpl as $item){
		switch(true){
			//dla autoodliczania (autoincrement)
			case (strtolower($item) === 'autoincrement'):
				$return['autoincrement'] = true;
				break;
			//dla danych domyślnych dla kolumny
			case substr($item, 0, 8) === 'default(':
				$return['defaultData'] = $this->__clearString(core::$library->string->between($item, '(', ')', 0));
				break;
		}
	}
	// var_dump($columnExpl, count($columnExpl), $return['length']);
	return $return; //zwróćenie danych
};

//sprawdza poprawność danych dla kolumny oraz w miare potrzeby je koryguje
$this->___CheckingDataForAColumn = function(string $data, array $column){
	core::setError();
	$dataLength = strlen($data); //pobranie ilości znaków ze zmiennej $data
	switch($column['type']){ //sprawdzanie i modyfikowanie danych dla poszczególnych typów tabel
		case 'text': //dla text
			$dataLength = 0; //zabezpieczenie aby kod nie sprawdzał ilości znaków
			$data = (string)$data; //modyfikowanie danych na string
			break;
		case 'int':
		case 'integer': //dla integer (liczby)
			$data = intval($data); //modyfikowanie na liczbe
			$column['type'] = 'integer'; //poprawka danych
			break;
		case 'bool':
		case 'boolean': //dla boolean (true/false)
			$dataLength = 0; //pomijanie sprawdzania długości znaków
			$data = boolval($data); //konwenterowanie na typ bool
			$column['type'] = 'boolean'; //poprawka danych
			break;
	}
	$dataType = gettype($data); //pobranie typu danych
	if($dataLength > $column['length']) //jeżeli dane posiadają za dużo znaków
		return core::setError(53, 'error data length', 'column: '.$column['name'].', length: '.$dataLength.'/'.$column['length']);
	if($dataType <> $column['type'] and $column['type'] <> 'text') //sprawdzenie typu i pominięcie jeżeli text
		return core::setError(52, 'error data type', 'column: '.$column['name'].', type: '.$column['type'].' ('.$dataType.')');
	return $data; //zwrócenie skorygowanej wartości
	// core::$library->debug->print_r(['$data' => $data, '$column' => $column, '$dataLength' => $dataLength]);
};

//wyszukiwanie i zwracanie tylko poszukiwanych danych
//$dataArray = ['nazwa kolumny' => 'wartosc', ...]
//$whereData = ['where1', 'where2', ...] np. ['id=4', 'count>4']
//$option:
//arrayValues = true //zwraca dane przez array_values (odnowione posortowane klucze)
$this->___whereData = function(array $dataArray, array $whereData, array $option = []){
	if(!isset($option['arrayValues'])) $option['arrayValues'] = true; //odnawianie kluczy
	foreach($whereData as $wData){ //pętla danych where
		preg_match_all("/(.+) ?([=|%|>|<]) ?(.+)/msi", $wData, $dataMatches, PREG_SET_ORDER, 0); //wyszukiwanie danych
		if(count($dataMatches) == 0) continue; //jeżeli błąd w wyrazie wyszukiwania to pominięcie
		$whereFind = [0 => $this->__clearString($dataMatches[0][1]), 1 => $dataMatches[0][2], 2 => $this->__clearString($dataMatches[0][3])]; //wczytanie danych do tablicy i ich wyczyszczenie
		$this->__log(['$whereFind' => $whereFind, '$dataArray' => $dataArray]);
		if(!isset($dataArray[array_keys($dataArray)[0]][$whereFind[0]])) //sprawdzenie czy kolumna istnieje w pierwszym znalezionym rekordzie
			return core::setError(21, 'column not found', 'name: '.$whereFind[0]);
		switch($whereFind[1]){ //przełącznik dla danych wyszukiwanych
			case '=': // równa się
				foreach($dataArray as $dataID => $dataData) //pętla danych
					if($dataData[$whereFind[0]] <> $whereFind[2]) //jeżeli są inne
						unset($dataArray[$dataID]);
				break;
			case '%': //zawiera
				foreach($dataArray as $dataID => $dataData) //pętla danych
					if(core::$library->string->strpos($dataData[$whereFind[0]], $whereFind[2]) == -1) //jeżeli nie zawierają ciągu
						unset($dataArray[$dataID]);
				break;
			case '>': // większe niż
				foreach($dataArray as $dataID => $dataData) //pętla danych
					if($dataData[$whereFind[0]] <= $whereFind[2]) //jeżeli są mniejsze lub równe
						unset($dataArray[$dataID]);
				break;
			case '<': // mniejsze niż
				foreach($dataArray as $dataID => $dataData) //pętla danych
					if($dataData[$whereFind[0]] >= $whereFind[2]) //jeżeli są większe lub równe
						unset($dataArray[$dataID]);
				break;
		}
		// $this->__log(['$whereFind' => $whereFind]);
	}
	// $this->__log(['$dataArray' => $dataArray, '$whereData' => $whereData]);
	return $option['arrayValues']==true?array_values($dataArray):$dataArray; //zwrócenie danych
}
?>