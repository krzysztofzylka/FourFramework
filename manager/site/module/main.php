<h1>Lista modułów</h1>
<table class="title border">
	<tr>
		<td>Nazwa</td>
		<td>Wersja</td>
		<td>Opis</td>
		<td>Język</td>
		<td>Opcje</td>
	</tr>
<?php
$scan = scandir($core->path['dir_module']);
$scan = array_diff($scan, ['.', '..', '.htaccess']);
foreach($scan as $name){
	$path = $core->path['dir_module'].$name.'/config.php';
	$config = include($path);
	$option = (isset($config['adminpanel']) and !empty($config['adminpanel']))?'<a href="index.php?type=module&page=module&name='.$name.'" class="button">AdminPanel</a> ':'';
	if(optionRead('module_show_dev_allConfig')==1)
		$option .= '<a href="index.php?type=module&page=dev_config&name='.$name.'" class="button">DEV: Wyświetl konfigurację</a> ';
	if(optionRead('module_show_dev_allvardump')==1)
		$option .= '<a href="index.php?type=module&page=dev_vardump&name='.$name.'" class="button">DEV: Wyświetl var_dump</a> ';
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