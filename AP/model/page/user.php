<?php
return new class(){
	public function zmienHaslo(){
		$haslo = htmlspecialchars($_POST['haslo']);
		$haslo2 = htmlspecialchars($_POST['haslo2']);
		$haslo2_re = htmlspecialchars($_POST['haslo2_re']);
		if(strlen($haslo2) < 8)
			echo '<div class="alert alert-danger" role="alert">Hasło musi posiadać minimum 8 znaków</div>';
		elseif($haslo2 <> $haslo2_re)
			echo '<div class="alert alert-danger" role="alert">Podane hasła się nie zgadzają</div>';
		else{
			core::$module['account']->changePassword(core::$module['account']->userData['login'], $haslo, $haslo2);
			if(core::$error[0] > -1){
				switch(core::$error[0]){
					case 1:
						echo '<div class="alert alert-danger" role="alert">Nie znaleziono takiego użytkownika</div>';
						break;
					case 2:
						echo '<div class="alert alert-danger" role="alert">Podane hasło się nie zgadza</div>';
						break;
					default:
						echo '<div class="alert alert-danger" role="alert">Błąd zmiany hasła</div>';
						break;
				}
			}else
				echo '<div class="alert alert-success" role="alert">Poprawnie zmieniono hasło</div>';
		}
	}
}
?>
