<?php
$file = htmlspecialchars(basename($_GET['file']));
$path = core::$path['log'].$file.'.log';
if(!file_exists($path))
	header('location: index.php?page=404');
?>
<div class="content-header">
	<div class="container-fluid">
		<h1 class="m-0 text-dark">Podgląd pliku <?php echo $file ?></h1>
	</div>
</div>

<div class="content-header">
	<div class="container-fluid">
		<div class="card">
			<?php
			if(core::$library->string->strpos($file, 'core_error_') === 0){
				$data = explode(PHP_EOL, file_get_contents($path));
				echo '<div class="card-body p-0" style="overflow: auto;">
				<table class="table table-sm">
					<thead>
						<tr>
							<th style="width: 15px">#</th>
							<th style="width: 160px">Data</th>
							<th style="width: 15px">Numer</th>
							<th>Nazwa</th>
							<th>Opis</th>
						</tr>
					</thead>
					<tbody>';
					foreach($data as $id=>$item){
						$date = core::$library->string->between($item, '[', ']');
						if($date === null)
							continue;
						$numer = core::$library->string->between($item, '[', ']', 1);
						$nazwa = core::$library->string->between($item, '[', ']', 2);
						$opis = core::$library->string->between($item, '[', ']', 3);
						$debug = core::$library->string->between($item, '[', ']', 4);
						echo '<tr>
							<td><a href="?page=framework_logi&debug='.$id.'&debug_file='.$_GET['file'].'">'.$id.'</a></td>
							<td>'.$date.'</td>
							<td>'.$numer.'</td>
							<td>'.$nazwa.'</td>
							<td>'.$opis.'</td>
						</tr>';
					}
					echo '</tbody>
				</table>
				</div>';
			}elseif(core::$library->string->strpos($file, 'php_error') === 0){
				echo '<div class="card-body p-0" style="overflow: auto;">
				<table class="table table-sm">
					<thead>
						<tr>
							<th style="width: 15px">#</th>
							<th style="width: 250px">Data</th>
							<th>Błąd</th>
						</tr>
					</thead>
					<tbody>';
				$explode = explode(PHP_EOL, file_get_contents($path));
				$id = 0;
				$string = '';
				$data = '';
				for($i=0; $i<=count($explode)-1; $i++){
					$line = $explode[$i];
					if($data === '')
						$data = core::$library->string->between($line, '[', ']');
					$string .= str_replace('['.$data.']', '', $line);
					$line_check = isset($explode[$i+1])?(core::$library->string->between($explode[$i+1], '[', ']')===null?true:false):false;
					if($line_check === true){
						$string .= '<br />';
						continue;
					}
					$data = explode(' ', $data);
					$data2 = count($data)>=2?$data[0].' '.$data[1]:implode(' ', $data);
					echo '<tr>
						<td>'.$id.'</td>
						<td>'.$data2.'</td>
						<td>'.$string.'</td>
					</tr>';
					$id++;
					$string = '';
					$data = '';
				}
				echo '</tbody>
				</table>
				</div>';
			}else
				echo '<div class="card-body">'.nl2br(file_get_contents($path)).'</div>';
			?>
		</div>
	</div>
</div>