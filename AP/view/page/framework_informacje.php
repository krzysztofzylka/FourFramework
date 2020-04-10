<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Informacje o Framework</h1>
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
						<h3 class="card-title">Wersja</h3>
					</div>
					<div class="card-body">
						<?php echo core::$info['version'] ?>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title">Data wydania</h3>
					</div>
					<div class="card-body">
						<?php echo core::$info['releaseDate'] ?>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title">Modułów</h3>
					</div>
					<div class="card-body">
						<?php
						$scandir = scandir(core::$path['module']); //scan library dir
						$scandir = array_diff($scandir, ['.', '..', '.htaccess']);
						echo count($scandir);
						?>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title">Bibliotek</h3>
					</div>
					<div class="card-body">
						<?php
						echo count(core::$library->__list());
						?>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title">API</h3>
					</div>
					<div class="card-body">
						<?php
						$scandir = scandir(core::$path['library'].'api/'); //scan library dir
						$scandir = array_diff($scandir, ['.', '..', '.htaccess']);
						echo count($scandir);
						?>
					</div>
				</div>
			</div>
        </div>
	</div>
</div>
