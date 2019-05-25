<?php
if(!isset($_GET['file'])){
	echo '<h1>Błąd</h1>Nie znaleziono logu';
}else{
	$name = htmlspecialchars($_GET['file']);
	$path = $core->path['dir_log'].basename($name).'.log';
	if(!file_exists($path)){
		echo '<h1>Błąd</h1>Nie znaleziono logu';
	}else{
		echo "<h1>Podgląd logu ".$name."</h1>
		<pre>".file_get_contents($path)."</pre>";
	}
}
?>