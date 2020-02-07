<?php
if(!isset($_SESSION['fdbConnect']) or !isset($_GET['name']))
	header('location: ?page=fdbeditor&fdb=404');
$name = htmlspecialchars($_GET['name']);
$column = core::$library->db->request('ADVENCED GET column FROM '.$name, $_GLOBALS['fdbEditor']);
$ai = core::$library->db->request('ADVENCED GET autoincrement FROM '.$name, $_GLOBALS['fdbEditor']);
$column_name = [];
?>
<div class='card-header'>
	Tabela <?php echo $name ?>
	<div class="card-tools">
		<button type="button" class="btn btn-tool" data-toggle="tooltip" data-placement="left" title="Edytuj tabelę" disabled><i class="fas fa-edit"></i></button>
		<a href="?page=fdbeditor&fdb=rekord&tabela=<?php echo $name ?>&type=dodaj"><button type="button" class="btn btn-tool" data-toggle="tooltip" data-placement="left" title="Dodaj rekord"><i class="fas fa-plus"></i></button></a>
	</div>
</div>
<div class="card-body table-responsive p-0">
	<table class="table table-hover table-sm">
		<thead>
			<tr>
				<?php
				for($i=0; $i<=$column['count']-1; $i++){
					$col = $column[$i];
					$width = -1;
					if($col['type'] === 'boolean')
						$width = 80;
					if($ai['id']==$i)
						$width = 80;
					echo '<th style="'.($ai['id']==$i?'text-decoration: underline;':'').' '.($width<>-1?'width: '.$width.'px':'').'">'.$col['name'].'</th>';
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
				if($ai['ai'] === true)
					$where = '`'.$column[$ai['id']]['name'].'`=`'.$item[$column[$ai['id']]['name']].'`';
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
					<i class="fas fa-minus-square" data-toggle="tooltip" data-placement="left" title="Usuń rekord"></i>
				</td>
				</tr>';
			}
			?>
		</tbody>
	</table>
</div>