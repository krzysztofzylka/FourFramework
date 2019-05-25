<?php
$name = htmlspecialchars($_GET['name']);
?>
<h1>DEV: Wyświetlanie konfiguracji modułu <?php echo $name ?></h1>
<table class="title border twoData">
	<tr>
		<td>Nazwa</td>
		<td>Wartość</td>
	</tr>
	<?php
	$path = $core->path['dir_module'].$name.'/';
	$config = include($path.'config.php');
	foreach($config as $name => $value){
		if(is_array($value)){
			if($name == "include"){
				$data = "";
				foreach($value as $fname){
					$data .= $fname.' ('.(file_exists($path.$fname)?'Znaleziono':'Nie znaleziono').')'.PHP_EOL;
				}
				$value = $data;
			}else
				$value = "{array}";
		}
		if($name == 'main_file')
			$value = $value.' ('.(file_exists($path.$value)?'Znaleziono':'Nie znaleziono').')';
		echo '<tr>
			<td>'.$name.'</td>
			<td>'.$value.'</td>
		</tr>';
	}
	?>
</table>