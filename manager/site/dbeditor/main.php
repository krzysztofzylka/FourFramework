<h1 class="bg">Opcje bazy danych</h1>
<div class="menu">
	<a href='index.php?type=dbeditor&page=main&func=tabele'>Tabele</a>
	<a href='index.php?type=dbeditor&page=main&func=createTable'>Utwórz tabelę</a>
	<a href='index.php?type=dbeditor&page=main&func=script'>Wykonaj skrypt</a>
	<a href='index.php?type=dbeditor&page=main&func=option'>Ustawienia bazy</a>
</div>
<hr />
<?php
$func = isset($_GET['func'])?htmlspecialchars($_GET['func']):'tabele';
switch($func){
	case 'script':
		if(isset($_POST['script'])){
			$return = $db->script(htmlspecialchars($_POST['script']));
			echo '<div class="message">Wykonano skrypt '.$_POST['script'].'<br />Odpowiedź:<br />'.$return.'</div>';
		}
		echo '<form method="POST">
			<input style="width: 100%" name="script" />
			<input type="submit" style="width: 100%" />
		</form>';
		break;
	case 'tabele':
		if(isset($_GET['check'])){
			$check = $db->checkTable($_GET['check']);
			if($check==='')
				echo '<div class="message green">Tabela jest poprawna</div>';
			else
				echo '<div class="message red">Znaleziono błąd w tabeli: <br />'.$check.'</div>';
		}
		if(isset($_GET['delete'])){
			$db->deleteTable($_GET['delete']);
			echo '<div class="message green">Poprawnie usunięto tabelę</div>';
		}
		$tableList = $db->tableList();
		$hideList = explode('|', optionRead('dbeditor_hideTable'));
		$tableList = array_diff($tableList, $hideList);
		echo '<table>
		<tr>
			<th>Nazwa</th><th>Rozmiar</th><th>Opcje</th>
		</tr>';
		foreach($tableList as $name){
			echo '<tr>
				<td><a href="index.php?type=dbeditor&page=table&table='.$name.'">'.$name.'</a></td>
				<td>
					'.($core->libraryExists('memory')?$core->library->memory->formatBytes(filesize($DBpath.$name.'.FDB')):filesize($DBpath.$name.'.FDB')).'
				</td>
				<td>
					<a href="index.php?type=dbeditor&page=main&delete='.$name.'" onclick="return confirm(\'Czy na pewno usunąć tabelę '.$name.'? Tej operacji nie można cofnąć\')">Usuń</a> 
					<a href="index.php?type=dbeditor&page=main&func=tabele&check='.$name.'">Sprawdź</a>
				</td>
			</tr>';
		}
		echo '</table>';
		break;
	case 'createTable':
		if(isset($_POST['createTable'])){
			unset($_POST['createTable']);
			$name = $_POST['name'];
			unset($_POST['name']);
			$data = [];
			foreach($_POST as $item)
				if($item <> '') array_push($data, $item);
			$db->createTable($name, $data);
			echo '<div class="message green">Poprawnie utworzono tabelę '.$name.'</div>';
		}
		echo '<form method="POST">
			<b>Nazwa tabeli</b><br />
			<input type="text" name="name" /><br />
			<br />
			<b>Kolumny</b><br />';
			for($i=1;$i<=10;$i++){
				echo 'Kolumna '.$i.'<br /><input type="text" name="col_'.$i.'" /><br />';
			}
		echo '<br />
		<input type="submit" value="Dodaj" name="createTable" /></form>';
		break;
	case 'option':
		if(isset($_POST['saveOption'])){
			optionWrite('dbeditor_hideTable', htmlspecialchars($_POST['hideTable']));
		}
		echo '<form method="POST">
		<table>
			<tr>
				<td>Ukryte tabele w DBEditor</td>
				<td><input type="text" value="'.optionRead('dbeditor_hideTable').'" name="hideTable"></td>
			</tr>
		</table>
		<input type="submit" value="Zapisz ustawienia" name="saveOption" />
		</form>';
		break;
}
?>