<?php
//dla użytkownika
$this->version = '1.2.0a'; //wersja rozszerzenia
$this->tableVersion = '1.2'; //wersja bazy danych
$this->lastInsertID = null; //ID ostatniego dodanego elementu
$this->conn = null; //ostatnie poprawne połączenie

//dla skryptów
$this->basePath = core::$path['base'].'db/'; //ścieżka do folderu z bazami danych
$this->connection = []; //tablica z aktywnymi połączeniami
$this->activeConnect = null; //aktywne połączenia (dla potrzeb wewnętznych wykonywania kodu)
$this->passwordCryptAlg = 'sha256'; //algorytm szyfrowania haseł bazy danych

//tablice z danymi
$this->acceptType = ['string', 'integer', 'boolean', 'text']; //akceptowane typy ciągów danych

//regexp
$this->regex = [
	'(SELECT) ?(.+?) ?FROM (.*) [WHERE]+ ?(.*)', '(SELECT) ?(.+?) ?FROM (.*)', //select
	'(ADD DATA TO) (.+) \((.+)\) VALUES \((.+)\)', //add data to
	'(CREATE TABLE) (.*?) {(.*?)}', //create table
	'(UPDATE) (.+) SET (.+) [WHERE]+ (.*)?', '(UPDATE) (.+) SET (.+)', //update
	'(DELETE) FROM (.+) [WHERE]+ (.*)?', '(DELETE) FROM (.+)', //delete
	'(ADVENCED) (GET|SET) (.+) FROM (.+)', '(ADVENCED) (GET|SET) (.+)', //ADVENCED
	'(ALTER TABLE) (.+) (ADD) (.+)', //ALTER TABLE ADD COLUMN
	'(REPAIR TABLE) (.+)' //REPAIR TABLE
];

//dla programistów
$this->advencedLog = false; //wyświetlanie wszystkich logów bazy danych (domyślnie FALSE)
$this->saveDBFile = true; //czy kod ma zapisywać zmiany do plików bazy danych (domyślnie TRUE)
?>