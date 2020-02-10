<div class='card-header'>
	Tworzenie nowej bazy danych
</div>
<div class='card-body'>
	<?php
	if(isset($_POST['nazwa']) and isset($_POST['haslo'])){
		$name = htmlspecialchars($_POST['nazwa']);
		$haslo = htmlspecialchars($_POST['haslo']);
		core::$library->db->createDatabase($name, $haslo);
		switch(core::$error[0]){
			case -1:
				echo '<div class="alert alert-success" role="alert">Poprawnie utworzono bazę danych</div>';
				break;
			case 1:
				echo '<div class="alert alert-danger" role="alert">Baza danych o takiej nazwie już istnieje</div>';
				break;
		}
	}
	?>
	<form method="POST">
		<div class="form-group">
			<label>Nazwa bazy danych</label>
			<input type="text" name="nazwa" class="form-control" placeholder="Nazwa" />
		</div>
		<div class="form-group">
			<label>Hasło</label>
			<input type="password" name="haslo" class="form-control" placeholder="Hasło" />
		</div>
		<button type="submit" class="btn btn-primary">Utwórz bazę danych</button>
	</form>
</div>