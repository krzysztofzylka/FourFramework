<table class="border title">
	<tr>
		<td>Nazwa</td>
		<td>Wartość</td>
	</tr>
	<tr>
		<td>Wersja</td>
		<td><?php echo $config['version'] ?></td>
	</tr>
	<tr>
		<td>Nazwa sesji z ID zalogowanego</td>
		<td><?php echo $module->sessionName ?></td>
	</tr>
	<tr>
		<td>Nazwa sesji z IP zalogowanego</td>
		<td><?php echo $module->sessionNameIP ?></td>
	</tr>
	<tr>
		<td>Algorytm kodujący hasło</td>
		<td><?php echo $module->hashAlgoritm ?></td>
	</tr>
</table>
<br />
<h1>Wymagania modułu</h1>
<?php
if((bool)$core->__autoConfigDB('lib_database_autostart')){
	$check1 = 'green';
	$core->library->database->connError = false;
	$config = [
		'type' => $core->__autoConfigDB('lib_database_connect_type'),
		'host' => $core->__autoConfigDB('lib_database_connect_host'),
		'name' => $core->__autoConfigDB('lib_database_connect_name'),
		'login' => $core->__autoConfigDB('lib_database_connect_login'),
		'password' => $core->__autoConfigDB('lib_database_connect_password'),
		'sqlite' => $core->__autoConfigDB('lib_database_connect_sqlite'),
		'port' => $core->__autoConfigDB('lib_database_connect_port'),
		'charset' => 'utf8'
	];
	$core->library->database->connect($config);
	if($core->lastError['number'] == -1){
		$check2 = 'green';
		$query = $core->library->database->query('SELECT 1 FROM uzytkownicy LIMIT 1');
		if($query === false)
			$check3 = 'red';
		else
			$check3 = 'green';
	}else{
		$check2 = 'red';
	}
}else{
	$check1 = 'red';
	$check2 = 'red';
	$check3 = 'red';
}
?>
<div class="message <?php echo $check1 ?>">Automatyczne wczytywanie biblioteki <b>database</b> oraz jego konfiguracji</div>
<div class="message <?php echo $check2 ?>">Poprawne połączenie z bazą danych</div>
<div class="message <?php echo $check3 ?>">Utworzona baza danych (uzytkownicy) w bazie danych</div>
<?php
if($check3 == 'red'){
	if(isset($_POST['create_table'])){
		$sql = "CREATE TABLE uzytkownicy (
			id int NOT NULL AUTO_INCREMENT,
			login varchar(48) NOT NULL,
			haslo varchar(128) NOT NULL,
			PRIMARY KEY (id)
		)";
		$core->library->database->exec($sql);
		header("Refresh:0");
	}
	echo '<form method="POST">
		<input type="submit" value="Utwórz bazę danych" name="create_table" />
	</form>';
}
?>