<?php
if(file_exists('page2/panel.php')){
	header('location: index.php?p=panel');
}
?>
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Panel główny</h1>
			</div>
		</div>
	</div>
</div>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-3">
				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title">Wersja panelu administratora</h3>
					</div>
					<div class="card-body">
						<?php echo $_GLOBALS['wersja'] ?>
					</div>
				</div>
			</div>
        </div>
	</div>
</div>