<h1><?php echo $lang->get('modulelist') ?></h1>
<table class="title border">
	<tr>
		<td><?php echo $lang->get('name') ?></td>
		<td><?php echo $lang->get('version') ?></td>
		<td><?php echo $lang->get('description') ?></td>
		<td><?php echo $lang->get('lang') ?></td>
		<td><?php echo $lang->get('option') ?></td>
	</tr>
<?php
$scan = scandir($core->path['dir_module']);
$scan = array_diff($scan, ['.', '..', '.htaccess']);
foreach($scan as $name){
	$path = $core->path['dir_module'].$name.'/config.php';
	$config = include($path);
	$option = (isset($config['adminpanel']) and !empty($config['adminpanel']))?'<a href="index.php?type=module&page=module&name='.$name.'" class="button">'.$lang->get('adminpanel').'</a> ':'';
	if(optionRead('module_show_dev_allConfig')==1)
		$option .= '<a href="index.php?type=module&page=dev_config&name='.$name.'" class="button">'.$lang->get('dev').': '.$lang->get('viewconfig').'</a> ';
	if(optionRead('module_show_dev_allvardump')==1)
		$option .= '<a href="index.php?type=module&page=dev_vardump&name='.$name.'" class="button">'.$lang->get('dev').': '.$lang->get('viewvardump').'</a> ';
	if(isset($config['changelog'])){
		$path = $core->path['dir_module'].$name.'/'.$config['changelog'];
		if(file_exists($path))
			$option .= '<a href="index.php?type=module&page=changelog&name='.$name.'" class="button">'.$lang->get('changelog').'</a>';
	}
	echo '<tr>
		<td>'.(isset($config['name'])?$config['name']:$name).'</td>
		<td>'.(isset($config['version'])?$config['version']:'').'</td>
		<td>'.(isset($config['description'])?$config['description']:'').'</td>
		<td>'.(isset($config['language'])?$config['language']:'').'</td>
		<td>'.$option.'</td>
	</tr>';
}
?>
</table>