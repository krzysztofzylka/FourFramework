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
		<button type="button" class="btn btn-tool" data-toggle="tooltip" data-placement="left" title="Edytuj tabelę" disabled>
			<i class="fas fa-edit"></i>
		</button>
		<a href="?page=fdbeditor&fdb=rekord&tabela=<?php echo $name ?>&type=dodaj"><button type="button" class="btn btn-tool" data-toggle="tooltip" data-placement="left" title="Dodaj rekord">
			<i class="fas fa-plus"></i>
		</button></a>
	</div>
</div>
<div class='card-body p-0'>
	<div class="card-body table-responsive p-0">
		<table class="table table-hover table-sm">
			<thead>
				<tr>
					<?php
					for($i=0; $i<=$column['count']-1; $i++){
						$col = $column[$i];
						echo '<th style="'.($ai['id']==$i?'text-decoration: underline;':'').'">'.$col['name'].'</th>';
						array_push($column_name, $col['name']);
					}
					?>
					<th style="width: 150px">Opcje</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$read = core::$library->db->request('SELECT FROM '.$name);
				foreach($read as $item){
					echo '<tr>';
					foreach($column_name as $columnName)
						echo '<td>'.$item[$columnName].'</td>';
					echo '<td>
						<i class="fas fa-edit disabled" data-toggle="tooltip" data-placement="left" title="Edytuj rekord"></i>
						<i class="fas fa-minus-square" data-toggle="tooltip" data-placement="left" title="Usuń rekord"></i>
					</td>
					</tr>';
				}
				?>
			</tbody>
		</table>
	</div>
</div>