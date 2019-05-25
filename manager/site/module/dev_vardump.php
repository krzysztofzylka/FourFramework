<?php
$name = htmlspecialchars($_GET['name']);
?>
<h1>Informacje modułu <?php echo $name ?></h1>
<?php
$core->loadModule($name);
if($core->lastError['number'] == -1){
	echo '<pre>';
	var_dump($core->module[$name]);
	echo '</pre>';
}else{
	echo 'Nie udało się wczytać modułu ('.$core->lastError['name'].')';
}
?>