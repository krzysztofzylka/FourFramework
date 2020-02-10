<?php
return new class(){
	public function __construct(){
		if(isset($_GET['type'])){ //jeżeli wybrano podstronę
			switch(htmlspecialchars($_GET['type'])){ //pobranie typu
				case 'informacje': //informacje o module
					if(!isset($_GET['name'])) //jeżeli nie wybrano modułu
						header('location: index.php?page=404'); //błąd 404
					core::loadView('framework_moduly_informacje'); //ładowanie widoku
					break;
				case 'adminpanel': //adminpanel modułu
					if(!isset($_GET['modul'])) //jeżeli nie wybrano modułu
						header('location: ?page=404'); //błąd 404
					core::loadView('framework_moduly_adminpanel'); //ładowanie widoku
					break;
				default:
					header('location: index.php?page=404'); //zwrócenie błędy
					break;
			}
		}else
			core::loadView('framework_moduly'); //ładowanie głównego widoku
	}
}
?>
