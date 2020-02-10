<?php
return new class(){
	public function __construct(){
		if(!core::$module['account']->checkUser()) //sprawdzenie czy użytkownik jest zalogowany
			$this->login(); //logowanie
		else
			$this->main(); //główny panel
	}
	private function login(){ //logowanie
		$smarty = core::$module['smarty']->smarty; //pobieranie smarty
		if(isset($_POST['login']) and isset($_POST['haslo'])){ //jeżeli użytkownik się loguje
			if(core::$module['account']->loginUser(htmlspecialchars($_POST['login']), htmlspecialchars($_POST['haslo']))) //spradzenie użytkownika
				header('location: index.php'); //poprawnie zalogowano
			else //błąd logowania
				$smarty->assign('error', 'Błędne dane logowania'); //błąd logowania
		}
		$smarty->display('login.tpl'); //ładowanie szablonu
	}
	private function main(){ //główny panel
		$smarty = core::$module['smarty']->smarty; //pobieranie smarty
		$menu = core::loadModel('menu'); //ładowanie modelu menu
		$smarty->assign('menu', $menu->loadMenu()); //ładowanie menu
		if(!isset($_GET['page']) and !isset($_GET['p'])) //jeżeli nie wybrano żadnej strony
			$_GET['page'] = 'panel'; //ładowanie domyślnej strony
		if(isset($_GET['p'])){ //kontroler uzytkownika
			foreach(['controller', 'view', 'model'] as $name) //tworzenie ścieżek do folderów controller, view oraz model
				core::$path[$name] .= 'user/'; //podmiana ścieżek
			ob_start(); //rozpoczęcie ładowania
			core::loadController(htmlspecialchars($_GET['p'])); //ładowanie kontrolera
			if(core::$error[0] > -1) //jeżeli błąd
				core::loadView('404'); //ładowanie strony 404
			$data = ob_get_contents(); //pobieranie treści
			ob_end_clean(); //czyszczenie strony
			$smarty->assign('data', $data); //dodanie treści do strony
		}elseif(isset($_GET['page'])){ //kontroler admin panelu
			foreach(['controller', 'view', 'model'] as $name) //tworzenie ścieżek do folderów controller, view oraz model
				core::$path[$name] .= 'page/'; //podmiana ścieżek
			ob_start(); //rozpoczęcie ładowania
			core::loadController(htmlspecialchars($_GET['page'])); //ładowanie kontrolera
			if(core::$error[0] > -1) //jeżeli błąd
				core::loadView('404'); //ładowanie strony 404
			$data = ob_get_contents(); //pobieranie treści
			ob_end_clean(); //czyszczenie strony
			$smarty->assign('data', $data); //dodanie treści do strony
		}
		$smarty->display('main.tpl'); //ładowanie szablonu
	}
}
?>