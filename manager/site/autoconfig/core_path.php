<?php
$default = [
	 'dir_core' => 'core/',
	'file_core' => 'core/core.php',
	 'dir_library' => 'core/library/',
	 'dir_controller' => 'controller/',
	 'dir_model' => 'model/',
	 'dir_log' => 'core/base/log/',
	 'dir_log_php' => 'core/base/log/',
	 'dir_module' => 'module/',
	 'dir_template' => 'template/',
	 'dir_view' => 'view/',
	 'dir_temp' => 'core/base/temp/',
	 'dir_base' => 'core/base/',
	 'dir_db' => 'core/base/db/',
];
if(isset($_POST['optSave'])){
	foreach($_POST as $name => $value){
		$check = substr($name, 0, 5);
		if($check == 'path_')
			$core->__autoConfigDB('core_path_'.str_replace('path_', '', $name), $value);
	}
}
if(isset($_POST['optDefault'])){
	foreach($default as $name => $value){
		$core->__autoConfigDB('core_path_'.$name, $value);
	}
}
?>
<h1><?php echo $lang->get('changepathlist') ?></h1>
<form method="POST">
	<table class="title border">
	<tr>
		<td><?php echo $lang->get('name'); ?></td>
		<td><?php echo $lang->get('value'); ?></td>
	</tr>
	<?php
	foreach($core->path as $name => $value){
		$val = $default[$name];
		$check = $core->__autoConfigDB('core_path_'.$name);
		if($check <> false)
			$val = $check;
		echo '<tr>
			<td>'.$name.'</td>
			<td><input type="text" name="path_'.$name.'" value="'.$val.'" /></td>
		</tr>';
	}
	?>
	</table><br />
	<input type="submit" value="<?php echo $lang->get('saveoption'); ?>" name="optSave" />
	<input type="submit" value="<?php echo $lang->get('loaddefault'); ?>" name="optDefault" />
</form>