<?php
return $this->db = new class(){
	public function __construct(){
		core::setError();
		include('variable.php'); //pobranie zmiennych
		if(!file_exists($this->basePath)) //tworzenie folderu dla baz danych jeżeli nie istnieje
			mkdir($this->basePath, 0700, true);
		include('function/adding.php'); //wczytanie dodatkowych funkcji
			//__generateUniqueConnectionID - generowanie unikatowego ID połączenia
			//__clearString - czyszczenie ciągu
			//__log - log
			//__checkTable - sprawdzenie poprawności bazy
		include('function/file.php'); //funkcje operacji na plikach
			// ____saveDBFile - zapis pliku tabeli
			// ____readDBFile - odczyt pliku tabeli
		include('function/scriptAdding.php'); //wczytanie funkcji pomocniczych dla funkcji z pliku script.php 
			//___columnDataExplode - funkcja dla createTable
			//___checkColumnAndData - sprawdzenie poprawności wprowadzanych danych dla kolumny
			//___whereData - wyszukiwanie danych wg. skryptów
		include('function/script.php'); //wczytanie funkcji wykonywanych skryptów
			//_request - zapytanie (wewnętrzny kod)
			//_createTable - skrypt create table
			//_addDataTo - skrypt Add Data To
			//_selectData - wyświetlenie danych z tabeli
			//_deleteData - usunięcie danych z tabeli
			//_updateData - aktualizacja danych w tabeli
			//_advenced - opcje zaawansowane
		include('function/main.php'); //wczytanie głównych funkcji
			//connect - połączenie z bazą danych
			//createDatabase - utworzenie bazy danych
			//request - wykonanie zapytania
		include('function/conventer/conventer.php'); //wczytanie pliku dla funkcji convetner
			//conventer - konwenterowanie wersji bazy danych do najnowszej
	}
	public function __call($method, $arguments){ //funkcja wywołująca funkcje importowane z plików za pomocą include
        return call_user_func_array(Closure::bind($this->$method, $this, get_called_class()), $arguments);
    }
}
?>