<?php
if(!isset($_SESSION['fdbConnect']))
	header('location: ?page=fdbeditor&fdb=404');
$edycja = false;
$typ_danych = ['string', 'integer', 'text', 'boolean'];
?>
<form method='post'>
<div class="card-header">
	<?php
	switch($edycja){
		case false:
			echo 'Utworzenie nowej tabeli w bazie danych';
			break;
	}
	?>
</div>
<?php
if(isset($_POST['tabela_nazwa'])){
	$column = '';
	if(!isset($_POST['column_ai']))
		$_POST['column_ai'] = '';
	for($i=0; $i<=100; $i++){
		if(!isset($_POST['column'.$i.'_name'])) continue;
		$length = '';
		if($_POST['column'.$i.'_type'] <> 'text' and $_POST['column'.$i.'_type'] <> 'boolean')
			$length = $_POST['column'.$i.'_char'];
		if($column <> '')
			$column .= ', ';
		$column .= '`'.$_POST['column'.$i.'_name'].'` '.$_POST['column'.$i.'_type'].''.($length===''?'':'('.$length.')'.($_POST['column_ai']==='AI_'.$i?' autoincrement':''));
	}
	$script = "CREATE TABLE `".$_POST['tabela_nazwa']."` { ".$column." }";
	core::$library->db->request($script);
	echo '<div class="card-body">';
	if(core::$error[0] > -1)
		echo '<div class="alert alert-danger" role="alert">'.core::$error[1].'</div>';
	else
		echo '<div class="alert alert-success" role="alert">Poprawnie dodano kolumne, należy ponownie odświeżyć stronę</div>';
	echo '</div>';
}
?>
<div class="card-body">
	<div class="form-group">
		<label>Nazwa tabeli</label>
		<input type="text" class="form-control" name="tabela_nazwa" placeholder="Nazwa tabeli">
	</div>
</div>
<div class="card-body p-0">
	<table class="table table-hover table-sm">
		<thead>
			<tr>
				<th>Nazwa kolumny</th>
				<th>Ilość znaków</th>
				<th>Typ danych</th>
				<th width='50px'>AI</th>
			</tr>
		</thead>
		<tbody id='columnList'>
		</tbody>
	</table>
</div>
<div class="card-footer">
	<?php
	switch($edycja){
		case false:
			echo '<button class="btn btn-primary" style="margin-right: 5px; color: white;">Utwórz tabelę</button>';
			echo '<a class="btn btn-info" onclick="addColumn()">Dodaj kolumnę</a>';
			break;
	}
	?>
</div>
</form>
<script src='script/tabele_mod.js'></script>