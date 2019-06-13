<?php
$name = htmlspecialchars($_GET['name']);
?>
<h1><?php echo $lang->get('infomodule') ?> <?php echo $name ?></h1>
<?php
$core->loadModule($name);
if($core->lastError['number'] == -1){
	echo '<pre>';
	var_dump($core->module[$name]);
	echo '</pre>';
}else{
	echo $lang->get('errorloadingmodule').' ('.$core->lastError['name'].')';
}
?>