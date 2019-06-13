<?php
if(!isset($_GET['file'])){
	echo '<h1>'.$lang->get('error').'</h1>'.$lang->get('nofindlog');
}else{
	$name = htmlspecialchars($_GET['file']);
	$path = $core->path['dir_log'].basename($name).'.log';
	if(!file_exists($path)){
		echo '<h1>'.$lang->get('error').'</h1>'.$lang->get('nofindlog');
	}else{
		echo "<h1>".$lang->get('viewlog')." ".$name."</h1>
		<pre>".file_get_contents($path)."</pre>";
	}
}
?>