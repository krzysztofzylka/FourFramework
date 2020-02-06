<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Zmiana hasła użytkownika</h1>
			</div>
		</div>
	</div>
</div>
<div class="content">
	<div class="container-fluid">
		<?php
		if(isset($_POST['haslo'])){
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
		?>
		<form method="post">
			<div class="card">
				<div class="card-body">
					<div class="form-group">
						<label for="haslo">Aktualne hasło</label>
						<input type="password" class="form-control" id="haslo" name="haslo" placeholder="Aktualne hasło">
					</div>
					<div class="form-group">
						<label for="haslo2">Nowe hasło</label>
						<input type="password" class="form-control" id="haslo2" name="haslo2" placeholder="Nowe hasło">
					</div>
					<div class="form-group">
						<label for="haslo2_re">Powtórz nowe hasło</label>
						<input type="password" class="form-control" id="haslo2_re" name="haslo2_re" placeholder="Powtórz nowe hasło">
					</div>
					<button type="submit" class="btn btn-primary">Zmień hasło</button>
				</div>
			</div>
		</form>
	</div>
</div>