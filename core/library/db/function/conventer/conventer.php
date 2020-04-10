<?php
//konwenterowanie bazy danych
$this->convert = function(string $dbName, string $dbPassword = null){
	core::setError();
	$versionList = ['1.1', '1.2']; //lista wersji (od najstarszej do najnowszej - WAŻNE)
	$dbPath = core::$path['base'].'db/'.$dbName.'/'; //generowanie ścieżki do bazy danych
	if(!file_exists($dbPath)) //jeżeli baza danych nie istnieje
		return core::setError(1, 'DB not exists', $dbPath); //zwrócenie błędu
	//pobranie aktualnej wersji bazy danych
	$fromVersion = null; //aktualna wersja bazy danych
	foreach(array_diff(scandir($dbPath), ['.' , '..', 'passwd.php']) as $tableName) //pobranie do pętli listy plików tabel (poza ., .. oraz passwd.php)
		if(substr($tableName, strlen($tableName)-3) === 'fdb'){ //jeżeli plik to tabela FDB
			$tableConfig = json_decode(file($dbPath.$tableName)[0], true); //pobranie konfiguracji tabeli
			if(!is_array($tableConfig) or !isset($tableConfig['version'])) //jeżeli pobrana konfiguracja nie jest tabelą lub brak danych o wersji (błąd pliku lub konfiguracji)
				return core::setError(2, 'Error read config file'); //zwrócenie błędu
			$fromVersion = $tableConfig['version']; //pobranie aktualnej wersji tabeli
			break; //koniec pętli
		}
	$versionID = array_search($fromVersion, $versionList); //wyszukiwanie wersji z listy
	if($versionID === false) //jeżeli nie znaleziono wersji
		return core::setError(3, 'Version not found');
	if($versionID+1 === count($versionList)) //jeżeli jest już najnowsza wersja bazy danych
		return false;
	$toVersion = $versionList[$versionID+1]; //wersja do której ma być zrobiony upgrade
	$convFileName = str_replace('.', '_', $fromVersion).'to'.str_replace('.', '_', $toVersion).'.php'; //nazwa pliku z klasą konwenterującą ({stara_wersja}to{nowa_wersja}.php)]
	$config = [
		'dbName' => $dbName, //nazwa bazy danych
		'dbPassword' => $dbPassword, //hasło bazy danych
		'dbPath' => $dbPath //ścieżka bazy danych
	]; //zmienna z konfiguracją dla konwentera
	return include($convFileName); //wczytanie oraz uruchomienie kowentera
	// $this->__log(['$dbName' => $dbName, '$dbPassword' => $dbPassword, '$dbPath' => $dbPath, '$fromVersion' => $fromVersion, '$toVersion' => $toVersion, '$convFileName' => $convFileName, '$config' => $config]); //log
};
?>