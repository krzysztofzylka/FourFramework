<br />
<div class="content">
	<div class="container-fluid">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Pliki logów</h3>
			</div>
			<div class="card-body p-0">
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Nazwa</th>
							<th style="width: 180px">Ostatni zapis</th>
							<th style="width: 40px">Rozmiar</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$scandir = scandir(core::$path['log']); //scan library dir
						foreach($scandir as $fname){ //file loop
							if(core::$library->string->strpos($fname, '.log') == -1) //if file
								continue;
							$path = core::$path['log'].$fname;
							$fname = str_replace('.log', '', $fname); //change name
							echo '<tr>
								<td><a href="?page=framework_logi&file='.$fname.'">'.$fname.'</a></td><td>'.date("Y-m-d H:i:s", filemtime($path)).'</td><td>'.core::$library->memory->formatBytes(filesize($path)).'</td>
							</tr>';
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>