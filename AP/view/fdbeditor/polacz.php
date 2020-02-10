<div class='card-header'>
	Łączenie z bazą danych
</div>
<div class='card-body'>
	<?php
	if(isset($_POST['nazwa']) and isset($_POST['haslo'])){
		$nazwa = htmlspecialchars($_POST['nazwa']);
		$haslo = htmlspecialchars($_POST['haslo']);
		$connect = core::$library->db->connect($nazwa, $haslo);
		switch(core::$error[0]){
			case -1:
				echo '<div class="alert alert-success" role="alert">Poprawnie połączono z bazą, strona powinna zostać odświeżona, jeżeli nie stało się to automatycznie, wciśnij <a href="?page=fdbeditor&fdb=tabele">TUTAJ</a></div>';
				$_SESSION['fdbConnect'] = [$nazwa, $haslo];
				header('location: index.php?page=fdbeditor&fdb=tabele');
				break;
			case 1:
				echo '<div class="alert alert-danger" role="alert">Baza danych nie istnieje</div>';
				break;
			case 2:
				echo '<div class="alert alert-danger" role="alert">Brak pliku passwd.php w folderze z bazą danych</div>';
				break;
			case 3:
				echo '<div class="alert alert-danger" role="alert">Hasło do bazy jest niepoprawne</div>';
				break;
		}
	}
	?>
	<form method="POST">
		<div class="form-group">
			<label>Wybierz bazę danych</label>
			<select class="form-control" name="nazwa">
				<?php
				$list = core::$library->db->databaseList();
				foreach($list as $name=>$value)
					echo '<option value="'.$name.'">'.$name.'</option>';
				?>
			</select>
		</div>
		<div class="form-group">
			<label>Hasło</label>
			<input type="password" name="haslo" class="form-control" placeholder="Hasło" />
		</div>
		<button type="submit" class="btn btn-primary">Połącz z bazą danych</button>
	</form>
</div>