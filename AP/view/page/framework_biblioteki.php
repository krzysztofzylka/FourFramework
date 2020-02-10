<br />
<div class="content">
	<div class="container-fluid">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Biblioteki</h3>
			</div>
			<div class="card-body p-0">
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Nazwa</th>
							<th style="width: 40px">Wersja</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$scandir = scandir('../core/library/'); //scan library dir
						foreach($scandir as $fname){ //file loop
							if(core::$library->string->strpos($fname, '.php') == -1) //if file
								continue;
							$fname = str_replace('.php', '', $fname); //change name
							echo '<tr>
								<td>'.$fname.'</td><td>'.core::$library->$fname->version.'</td>
							</tr>';
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">API</h3>
			</div>
			<div class="card-body p-0">
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Nazwa</th>
							<th style="width: 40px">Wersja</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$scandir = scandir('../core/library/api/'); //scan library dir
						foreach($scandir as $fname){ //file loop
							if(core::$library->string->strpos($fname, '.php') == -1) //if file
								continue;
							$fname = str_replace('.php', '', $fname); //change name
							$api = core::$library->api->start($fname);
							echo '<tr>
								<td>'.$fname.'</td><td>'.$api->version.'</td>
							</tr>';
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
