<?php if(isset($_GET['convert'])){
$name = htmlspecialchars($_GET['convert']); ?>
<div class='card-header'>
	Konwenterowanie bazy danych
</div>
<div class='card-body'>
	<?php
	if(isset($_POST['password'])){
		$convert = core::$library->db->convert($name, $_POST['password']);
		if(core::$error[0] > -1){
			echo '<div class="alert alert-danger" role="alert">Błąd podczas konwenterowania bazy danych</div>';
			core::$library->debug->print_r(core::$error);
		}elseif($convert === false)
			echo '<div class="alert alert-danger" role="alert">Baza danych posiada najnowszą wersję</div>';
		else
			echo '<div class="alert alert-success" role="alert">Poprawnie przekonwenterowano bazę danych</div>';
	}
	?>
	Wprowadzając hasło i klikając zatwierdź potwierdzasz chęć przekonwenterowania bazy danych o nazwie <b><?php echo $name ?></b> do najnowszej wersji <b><?php echo core::$library->db->tableVersion ?></b>.<br />
	<br />
	<b>Proszę zapoznać się z poniższymi informacjami:</b><br />
	- <b>Jeżeli z bazy danych korzysta nieoficjala biblioteka do obsługi baz danych może to spowodować problemy w działaniu oraz błąd aplikacji. (Jeżeli nie podmieniałeś pliku lub folderu core/library/db strona powinna dalej działać poprawnie)</b><br />
	- <b>Aktywujesz na własną odpowiedzialność, zalecane zrobienie kopii aktualnej bazy danych, funkcja konwenterująca NIE TWORZY kopii automatycznie (domyślnie bazy danych znajdują się w folderze <i>core/base/db</i></b><br />
	- <b>Twóca skryptu nie odpowiada za błędy powstałe podczas konwenterowania tabeli</b>
	<hr />
	<form method="POST" class="form">
		Hasło:<br />
		<input type="text" name="password" />
		<input type="submit" value="Zatwierdź" />
	</form>
</div>
<?php }else{ ?>
<div class='card-header'>
	Lista baz danych
</div>
<div class='card-body table-responsive p-0'>
<table class="table table-hover table-sm">
	<thead>
	<tr>
		<th>Nazwa bazy danych</th>
		<th width="100px">Tabel</th>
		<th width="100px">Hasło</th>
		<th width="100px">Wersja</th>
		<th width="100px">Opcje</th>
	</tr>
	</thead>
	<tbody>
	<?php
		$list = core::$library->db->databaseList(['version' => true]);
		foreach($list as $item){
			$dbPass = include($item['path'].'passwd.php');
			echo '<tr>
				<td>'.$item['name'].'</td>
				<td>'.count($item['table']).'</td>
				<td>'.(core::$library->crypt->hashCheck('', $dbPass)===true?'Nie':'Tak').'</td>
				<td>'.$item['version'].'</td>
				<td>';
				if(core::$library->db->tableVersion <> $item['version']){ //jeżeli inna wersja bazy danych niż aktualny skrypt (dla konwentera)
					echo '<a href="index.php?page=fdbeditor&fdb=dbList&convert='.$item['name'].'">Przekonwenteruj na aktualną wersję</a>';
				}
				echo '</td>
			</tr>';
		}
	?>
	</tbody>
</table>
</div>
<?php } ?>