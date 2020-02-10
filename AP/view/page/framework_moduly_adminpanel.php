<?php
$modul = htmlspecialchars($_GET['modul']);
?>
<div class="content-header">
	<div class="container-fluid">
		<h1 class="m-0 text-dark">Panel administracyjny modułu <?php echo $modul ?></h1>
	</div>
</div>
<div class="content">
	<div class="container-fluid">
		<?php
		core::$library->module->loadAdminPanel($modul);
		if(core::$error[0] > -1){
			echo '<div class="card card-danger">
				<div class="card-header">
					<h3 class="card-title">Błąd</h3>
				</div>
				<div class="card-body">
					'.core::$error[1].'
				</div>
			</div>';
		}
		?>
	</div>
</div>