<h1><?php echo $lang->get('apachemodulelist') ?></h1>
<?php
$require = ['mod_ssl'];
$module = apache_get_modules();
foreach($module as $name){
	echo $name."<br />";
}
$req = array_diff($require, $module);
foreach($req as $name){
	echo "<font style='color: red;'>".$name."</font><br />";
}
?>