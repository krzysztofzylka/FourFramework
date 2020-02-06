<?php
if(!isset($_SESSION['fdbConnect']))
	header('location: ?page=fdbeditor&fdb=404');
$list = core::$library->db->request('ADVENCED GET tableList', $_GLOBALS['fdbEditor']);
?>
<div class='card-header'>
	Lista tabel
</div>
<?php if(isset($_GET['script'])){
$script = htmlspecialchars($_GET['script']) ?>
<div class='card-body'>
	<b>Wykonano skrypt</b><br />
	<i><?php echo $script ?><br />
	<b>Odpowiedź:</b>
	<?php
	$request = core::$library->db->request($script, $_GLOBALS['fdbEditor']);
	var_dump($request);
	?>
</div>
<?php } ?>
<div class='card-body p-0'>
	<div class="card-body table-responsive p-0">
		<table class="table table-hover table-sm">
			<thead>
				<tr>
					<th>Nazwa</th>
					<th style="width: 150px">Rozmiar</th>
					<th style="width: 100px">Opcje</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach($list as $name){
					$path = core::$path['base'].'db/'.$_SESSION['fdbConnect'][0].'/'.$name.'.fdb';
					echo '<tr>
						<td><a href="?page=fdbeditor&fdb=tabela&name='.$name.'">'.$name.'</a></td>
						<td>'.core::$library->memory->formatBytes(filesize($path)).'</td>
						<td>
							<A href="?page=fdbeditor&fdb=tabele&script=REPAIR TABLE `'.$name.'`"><i class="fas fa-wrench disabled" data-toggle="tooltip" data-placement="left" title="Napraw tabelę"></i></a>
						</td>
					</tr>';
				}
				?>
			</tbody>
		</table>
	</div>
</div>