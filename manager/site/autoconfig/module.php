<?php
if(isset($_POST['optSave'])){
	$arr = [];
	foreach($_POST as $name => $value){
		if($value == 'atas'){
			$module_name = str_replace('autostart_', '', $name);
			array_push($arr, $module_name);
		}
	}
	$core->__autoConfigDB('module_autostart', htmlspecialchars(implode('|', $arr)));
	echo '<div class="message green">Poprawnie zapisano modułu do autostartu</div>';
}
?>
<h1>Automatyczne uruchamianie modułów</h1>
<form method="POST">
	<table class="title border">
		<tr>
			<td>Autostart</td>
			<td>Nazwa</td>
			<td>Wersja</td>
			<td>Opis</td>
			<td>Język</td>
		</tr>
	<?php
	$as = $core->__autoConfigDB('module_autostart');
	$as = explode('|', $as);
	$scan = scandir($core->path['dir_module']);
	$scan = array_diff($scan, ['.', '..', '.htaccess']);
	foreach($scan as $name){
		$path = $core->path['dir_module'].$name.'/config.php';
		$config = include($path);
		echo '<tr>
			<td align="center"><input type="checkbox" name="autostart_'.$name.'" value="atas" '.(in_array($name, $as)?'checked':'').' /></td>
			<td>'.(isset($config['name'])?$config['name']:$name).'</td>
			<td>'.(isset($config['version'])?$config['version']:'').'</td>
			<td>'.(isset($config['description'])?$config['description']:'').'</td>
			<td>'.(isset($config['language'])?$config['language']:'').'</td>
		</tr>';
	}
	?>
	</table>
	<br />
	<input type="submit" name="optSave" value="Zapisz ustawienia" />
</form>