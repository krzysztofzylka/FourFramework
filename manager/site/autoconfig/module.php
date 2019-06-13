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
	echo '<div class="message green">'.$lang->get('successsabemodtoas').'</div>';
}
?>
<h1><?php echo $lang->get('autostartmodule') ?></h1>
<form method="POST">
	<table class="title border">
		<tr>
			<td><?php echo $lang->get('autostart') ?></td>
			<td><?php echo $lang->get('name') ?></td>
			<td><?php echo $lang->get('version') ?></td>
			<td><?php echo $lang->get('description') ?></td>
			<td><?php echo $lang->get('lang') ?></td>
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
	<input type="submit" name="optSave" value="<?php echo $lang->get('saveoption') ?>" />
</form>