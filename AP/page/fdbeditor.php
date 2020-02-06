<?php
if(isset($_SESSION['fdbConnect']))
	$_GLOBALS['fdbEditor'] = core::$library->db->connect($_SESSION['fdbConnect'][0], $_SESSION['fdbConnect'][1]);
?>
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Edytor bazy danych na plikach FDB</h1>
			</div>
		</div>
	</div>
</div>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-3">
				<div class="card">
					<div class="card-header">
						<i class="fas fa-database"></i> Baza danych
					</div>
					<div class="card-body">
						<a href="?page=fdbeditor&fdb=utworz_baze"><button type="button" class="btn btn-block btn-primary btn-xs" style='margin-bottom: 5px;'><i class="fas fa-plus"></i> Utwórz nową bazę danych</button></a>
						<a href="<?php echo isset($_SESSION['fdbConnect'])?'#':'?page=fdbeditor&fdb=polacz' ?>"><button type="button" class="btn btn-block btn-default btn-xs <?php echo !isset($_SESSION['fdbConnect'])?'':'disabled' ?>" style='margin-bottom: 5px;'><i class="fas fa-plug"></i> Połącz z bazą danych</button></a>
						<a href="<?php echo !isset($_SESSION['fdbConnect'])?'#':'?page=fdbeditor&fdb=rozlacz' ?>"><button type="button" class="btn btn-block btn-info btn-xs <?php echo isset($_SESSION['fdbConnect'])?'':'disabled' ?>"><i class="fas fa-sign-out"></i> Rozłącz z bazą</button></a>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<i class="fas fa-table"></i> Tabele
					</div>
					<div class="card-body">
						<?php
						if(isset($_SESSION['fdbConnect'])){
							echo '<a href="?page=fdbeditor&fdb=tabele"><button type="button" class="btn btn-block btn-info btn-xs" style="margin-bottom: 5px;">Lista tabel</button></a>';
							$list = core::$library->db->request('ADVENCED GET tableList', $_GLOBALS['fdbEditor']);
							foreach($list as $name){
								echo '<a href="?page=fdbeditor&fdb=tabela&name='.$name.'"><button type="button" class="btn btn-block btn-default btn-xs" style="margin-bottom: 5px;">'.$name.'</button></a>';
							}
						}else
							echo 'Brak połączenia z bazą danych';
						?>
					</div>
				</div>
			</div>
			<div class="col-md-9">
				<div class="card">
					<?php
					if(isset($_GET['fdb'])){
						$fdb = htmlspecialchars(basename($_GET['fdb']));
						$page = 'page/fdbEditor/'.$fdb.'.php';
						if(!file_exists($page)){
							include('page/fdbEditor/404.php');
						}else
							include($page);
					}else
						include('page/fdbEditor/polacz.php');
					?>
				</div>
			</div>
		</div>
	</div>
</div>