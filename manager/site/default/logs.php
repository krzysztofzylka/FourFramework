<h1><?php echo $lang->get('logs') ?></h1>
<?php
$scan = scandir($core->path['dir_log']);
$scan = array_diff($scan, ['.htaccess', '.', '..']);
foreach($scan as $file){
	$name = str_replace('.log', '', $file);
	$path = $core->path['dir_log'].basename($name).'.log';
	echo '> <a href="index.php?type=default&page=logs_view&file='.$name.'">'.$name.'</a> ['.$core->library->memory->formatBytes(filesize($path)).']<br />';
}
?>