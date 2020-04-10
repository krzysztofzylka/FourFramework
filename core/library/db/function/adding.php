<?php
//generowanie unikatowego ID połączenia
$this->__generateUniqueConnectionID = function(){
	core::setError();
	while(true){ //pętla
		$uniqueID = core::$library->string->generateString(10, [true, true, false, false]); //generowanie ciągu znaków
		if(!isset($this->connection[$uniqueID])) return $uniqueID; //jeżeli unikatowy to zamykanie pętli
	}
};

//czyści tekst oraz używa funkcji removeQuotes
$this->__clearString = function(string $string){
	core::setError();
	$string = trim($string);
	$string = core::$library->string->removeQuotes($string);
	return $string;
};

//wyświetlenie logu
$this->__log = function($data){
	core::setError();
	if(!$this->advencedLog) return false; //jeżeli logi wyłączone
	core::$library->debug->print_r($data, false, 'AdvencedLog - DB library');
};

//sprawdzenie poprawności tabeli oraz czy istnieje
$this->__checkTable = function(string $tableName){
	core::setError();
	// $this->__log(['activeConnect' => $this->activeConnect]); //jeżeli brak połączenia z bazą
	if($this->activeConnect === null)
		return core::setError(3, 'connection not exists');
	if(!file_exists($this->connection[$this->activeConnect]['path'].$tableName.'.fdb')) //jeżeli plik fdb tabeli nie istnieje
		return core::setError(20, 'table is not already exists', $this->connection[$this->activeConnect]['path'].$tableName.'.fdb');
	return true;
};
?>