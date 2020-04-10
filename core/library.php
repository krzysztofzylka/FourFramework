<?php
return new class(){
	public $__list = [];
	public function __get($name){
		$path = core::$path['library'].$name;
		if(is_file($path.'.php')){ //jeżeli plik
			array_push($this->__list, $name);
			return include_once($path.'.php');
		}elseif(is_dir($path)){ //jeżeli zaawansowana biblioteka (folder)
			$initPath = $path.'/init.php';
			if(file_exists($initPath)){
				return include_once($initPath);
			}
		}
		core::setError(1, 'library file not found'); //błąd rdzenia
		trigger_error($name.' library not found', E_USER_ERROR); //błąd PHP
		return false;
	}
	public function __list(array $config = []){ //funkcja zwracająca listę bibliotek oraz opcjonalnie ich wersji
		if(!isset($config['version']))
			$config['version'] = false; //czy zwrócone mają być wersje bibliotek (wydłuża działanie, powoduje uruchomienie każdej biblioteki w celu pobrania wersji)
		$return = []; //zmienna ze zwracanymi danami
		$scanDir = array_diff(scandir(core::$path['library']), ['.', '..']); //skanowanie folderu z bibliotekami oraz usuwanie folderów . oraz ..
		foreach($scanDir as $name){ //pętla ze zeskanowanymi plikami oraz folderami
			$path = core::$path['library'].$name; //tworzenie ścieżki do pliku/folderu
			if(is_file($path)){ //jeżeli plik
				if(substr($name, strlen($name)-4) <> '.php') continue; //pomijanie jeżeli plik to nie plik PHP
				$libName = str_replace('.php', '', $name); //pobranie nazwy oraz usuwanie z ciągu rozszerzenia PHP
			}elseif(is_dir($path)){ //jeżeli folder
				if(!file_exists($path.'/init.php')) continue; //jeżeli folder nie posiada pliku inicjującego init.php to pomijanie
				$libName = $name; //pobranie nazwy
			}
			$libVersion = $config['version']===true?(core::$library->{$libName}->version):null; //domyślna wersja
			array_push($return, ['name' => $libName, 'version' => $libVersion]); //dodanie danych do zmiennej $return
		}
		return $return;
	}
}
?>