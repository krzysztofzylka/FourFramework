<?php
if(!isset($_SESSION['fdbConnect']) or !isset($_GET['type']) and !isset($_GET['tabela']))
	header('location: ?page=fdbeditor&fdb=404');
$type = htmlspecialchars($_GET['type']);
$tabela = htmlspecialchars($_GET['tabela']);
$column = core::$library->db->request('ADVENCED GET column FROM '.$tabela, core::$library->global->read('fdbConnect'));
$ai = core::$library->db->request('ADVENCED GET autoincrement FROM '.$tabela, core::$library->global->read('fdbConnect'));
?>
<div class="card-header">
<?php
switch($type){
	case 'dodaj':
		echo 'Dodawanie rekordu do tabeli '.$tabela;
		break;
	case 'edytuj':
		echo 'Edycja rekordu';
		if(!isset($_GET['where']))
			header('location: ?page=fdbeditor&fdb=404');
		$where = htmlspecialchars($_GET['where']);
		$rekord = core::$library->db->request('SELECT FROM '.$tabela.' WHERE '.$where);
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
		case 'edytuj':
			$dat = "";
			$id = 0;
			foreach($_POST as $key => $value){
				$dat .= '"'.$key.'"="'.$value.'"';
				if($id < count($_POST)-1){
					$dat .= ', ';
				}
				$id++;
			}
			$script = "UPDATE '".$tabela."' SET ".$dat." WHERE ".$where;
			core::$library->db->request($script);
			$rekord = core::$library->db->request('SELECT FROM '.$tabela.' WHERE '.$where);
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
				<td style="min-width: 300px">Wartość</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$id = 0;
			if(is_array($column)){
			foreach($column as $item){
				if(!is_array($item))
					continue;
				$value = '';
				$disable = false;
				if(core::$library->array->searchByKey($column, 'name', $ai['colName'])===$id){
					$value = 'AUTOINCREMENT';
					$disable = true;
				}
				if($type === 'edytuj'){
					//edycja rekordu
					$value = $rekord[0][$item['name']];
				}
				$form = "<input type='text' name='".$item['name']."' class='form-control' value='".$value."' ".($disable===true?'disabled':'')."/>";
				switch($item['type']){
					case 'text':
						$item['length'] = '-';
						$form = "<textarea name='".$item['name']."' class='form-control'>".$value."</textarea>";
						break;
					case 'boolean':
						$form = "<input type='text' name='".$item['name']."' id='table_".$item['name']."' class='form-control' value='".$value."' hidden/>
						<input type='checkbox' class='form-check-input' onclick='$(this).is(\":checked\") ? $(\"#table_".$item['name']."\").val(1) : $(\"#table_".$item['name']."\").val(0)' style='transform: scale(1.5);' ".($value===true?'checked':'')."/>";
						// $form .= '<td><input class="full" type="checkbox"  value="checked" onclick="$(this).is(\':checked\') ? $(\'#table_'.$column[$i]['name'].'\').val(1) : $(\'#table_'.$column[$i]['name'].'\').val(0)"/ '.($data[$column[$i]['name']]==1?'checked':'').'></td>';
						break;
				}
				echo "<tr>
					<td>".$item['name']."</td>
					<td>".$item['type']."</td>
					<td>".$item['length']."</td>
					<td>".$form."</td>
				</tr>";
				$id++;
			}
			}else{
				echo 'Błąd odczytu tabeli';
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
		case 'edytuj':
			echo 'Edytuj rekord';
			break;
	}
	?>
	</button>
</div>
</form>
