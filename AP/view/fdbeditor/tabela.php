<?php
if(!isset($_SESSION['fdbConnect']) or !isset($_GET['name']))
	header('location: ?page=fdbeditor&fdb=404');
$name = htmlspecialchars($_GET['name']);
$column = core::$library->db->request('ADVENCED GET column FROM '.$name, core::$library->global->read('fdbConnect'));
$ai = core::$library->db->request('ADVENCED GET autoincrement FROM '.$name, core::$library->global->read('fdbConnect'));
// var_dump($ai);
$column_name = [];
?>
<div class='card-header'>
	Tabela <?php echo $name ?>
	<div class="card-tools">
		<button type="button" class="btn btn-tool" data-toggle="tooltip" data-placement="left" title="Edytuj tabelę" disabled><i class="fas fa-edit"></i></button>
		<a href="?page=fdbeditor&fdb=rekord&tabela=<?php echo $name ?>&type=dodaj"><button type="button" class="btn btn-tool" data-toggle="tooltip" data-placement="left" title="Dodaj rekord"><i class="fas fa-plus"></i></button></a>
	</div>
</div>
<?php if(isset($_GET['script'])){
$script = htmlspecialchars($_GET['script']) ?>
<div class='card-body'>
	<b>Wykonano skrypt</b><br />
	<i><?php echo $script ?><br />
	<b>Odpowiedź:</b>
	<?php
	$request = core::$library->db->request($script, core::$library->global->read('fdbConnect'));
	var_dump($request);
	?>
</div>
<?php } ?>
<div class="card-body table-responsive p-0">
	<table class="table table-hover table-sm">
		<thead>
			<tr>
				<?php
				for($i=0; $i<=$column['count']-1; $i++){
					$col = $column[$i];
					// var_dump($col);
					$width = -1;
					if($col['type'] === 'boolean')
						$width = 80;
					if($ai['ai']==true)
						$width = 80;
					echo '<th style="'.($ai['colName']==$col['name']?'text-decoration: underline;':'').' '.($width<>-1?'width: '.$width.'px':'').'">'.$col['name'].'</th>';
					array_push($column_name, $col['name']);
				}
				?>
				<th style="width: 70px">Opcje</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$read = core::$library->db->request('SELECT FROM '.$name);
			foreach($read as $item){
				$where = '';
				if($ai['ai'] === true){
					$colID = core::$library->array->searchByKey($column, 'name', $ai['colName']); //id kolumny z autoodliczaniem
					$where = '`'.$column[$colID]['name'].'`=`'.$item[$column[$colID]['name']].'`';
				}else{
					$i = 1;
					foreach($item as $names=>$values){
						$where .= '`'.$names.'`=`'.$values.'`';
						if($i < count($item))
							$where .= ' and ';
						$i++;
					}
				}
				echo '<tr>';
				foreach($column_name as $columnName){
					$search = core::$library->array->searchByKey($column, 'name', $columnName);
					$colType = $column[$search]['type'];
					if($colType === 'boolean')
						if($item[$columnName] === true)
							$item[$columnName] = 'Tak';
						else
							$item[$columnName] = 'Nie';
					echo '<td>'.$item[$columnName].'</td>';
				}
				echo '<td>
					<a class="disabled" href="'.($where===''?'#':'?page=fdbeditor&fdb=rekord&tabela='.$name.'&type=edytuj&where='.$where).'"><i class="fas fa-edit" data-toggle="tooltip" data-placement="left" title="Edytuj rekord"></i></a>
					<a href="index.php?page=fdbeditor&fdb=tabela&name='.$name.'&script=DELETE FROM '.$name.' WHERE '.$where.'" onclick="return confirm(\'Czy na pewno chcesz usunąć ten rekord? Tego nie można cofnąć.\')"><i class="fas fa-minus-square" data-toggle="tooltip" data-placement="left" title="Usuń rekord"></i></a>
				</td>
				</tr>';
			}
			?>
		</tbody>
	</table>
</div>