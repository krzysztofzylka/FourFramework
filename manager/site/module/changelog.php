<h1><?php echo $lang->get('changelog') ?></h1>
<?php
$name = htmlspecialchars($_GET['name']);
$path = $core->path['dir_module'].$name.'/config.php';
$config = include($path);
if(isset($config['changelog'])){
	$path = $core->path['dir_module'].$name.'/'.$config['changelog'];
	if(file_exists($path))
		echo nl2br(file_get_contents($path));
}
?>