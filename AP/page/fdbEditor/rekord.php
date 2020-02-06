<?php
if(!isset($_SESSION['fdbConnect']) or !isset($_GET['type']) and !isset($_GET['tabela']))
	header('location: ?page=fdbeditor&fdb=404');
$type = htmlspecialchars($_GET['type']);
$tabela = htmlspecialchars($_GET['tabela']);
$column = core::$library->db->request('ADVENCED GET column FROM '.$tabela, $_GLOBALS['fdbEditor']);
$ai = core::$library->db->request('ADVENCED GET autoincrement FROM '.$tabela, $_GLOBALS['fdbEditor']);
?>
<div class="card-header">
<?php
switch($type){
	case 'dodaj':
		echo 'Dodawanie rekordu do tabeli '.$tabela;
		break;
	case 'edytuj':
		echo 'Edycja rekordu';
		break;
	default:
		header('location: ?page=fdbeditor&fdb=404');
		break;
}
?>
</div>
<?php
if(isset($_POST['submit'])){
	unset($_POST['submit']);
	switch($type){
		case 'dodaj':
			$col = "";
			$dat = "";
			$id = 0;
			foreach($_POST as $key => $value){
				$dat .= '"'.$value.'"';
				$col .= '"'.$key.'"';
				if($id < count($_POST)-1){
					$dat .= ', ';
					$col .= ', ';
				}
				$id++;
			}
			$script = "ADD DATA TO `".$tabela."` (".$col.") VALUES (".$dat.")";
			core::$library->db->request($script);
			break;
	}
	if(core::$error[0] > -1){
		echo '<div class="card-body"><div class="alert alert-danger" role="alert">Błąd wykonywania skryptu '.core::$error[1].'</div></div>';
	}else
		echo '<div class="card-body"><div class="alert alert-success" role="alert">Poprawnie wykonano komende</div></div>';
}
?>
<form method="post">
<div class="card-body table-responsive p-0">
	<table class="table table-hover table-sm">
		<thead>
			<tr>
				<td style="width: 200px;">Kolumna</td>
				<td style="width: 80px;">Typ tabeli</td>
				<td style="width: 80px;">Długość</td>
				<td>Wartość</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$id = 0;
			foreach($column as $item){
				if(!is_array($item))
					continue;
				echo "<tr>
					<td>".$item['name']."</td>
					<td>".$item['type']."</td>
					<td>".$item['length']."</td>
					<td><input type='text' placeholder='' name='".$item['name']."' style='width: 100%; min-width: 200px' ".($ai['id']===$id?'value="AUTOINCREMENT" disabled':'')."/></td>
				</tr>";
				$id++;
			}
			?>
		</tbody>
	</table>
</div>
<div class="card-body">
	<button type="submit" name="submit" class="btn btn-primary">
	<?php
	switch($type){
		case 'dodaj':
			echo 'Dodaj rekord';
			break;
	}
	?>
	</button>
</div>
</form>