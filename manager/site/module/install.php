<?php
$uid = htmlspecialchars($_GET['uid']);
?>
<h1><?php echo $lang->get('installmodule') ?></h1>
<pre>ERROR!
<?php
// $time = microtime(true);
// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Wyszukiwanie modułu o UID "'.$uid.'"<br />';
// $api = $core->_API('uid='.$uid);
// if($api['count'] == 0){
	// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Nie znaleziono modułu o takim UID';
// }else{
	// $module = $api['list'][0];
	// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Znaleziono moduł o nazwie <u>'.$module['name'].'</u><br />';
	// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Sprawdzenie czy plik jest dostępny na serwerze<br />';
	// $headers = get_headers($module['download']);
	// if(stripos($headers[0],"200 OK")==false)
		// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Nie znaleziono pliku na serwerze<br />';
	// else
		// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Znaleziono plik na serwerze<br />';
	// $path = $core->path['dir_temp'].'module.zip';
	// $extractPath = $core->path['dir_module'];
	// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Pobieranie pliku z URL "'.$module['download'].'" do ścieżki "'.$path.'"<br />';
	// $core->library->network->downloadFile($module['download'], $path);
	// if($core->lastError['number'] > -1)
		// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > <b>Błąd pobierania pliku ('.$core->lastError['name'].')</b><br />';
	// else{
		// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Wypakowywanie modułu do ścieżki "'.$extractPath.'"<br />';
		// $core->library->exZip->unzip($path, $extractPath);
		// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > Usuwanie pliku tymczasowego ("'.$path.'")<br />';
		// unlink($path);
		// echo '['.sprintf("%01.3f", (microtime(true)-$time)).'] > <b>Poprawnie zainstalowano moduł '.$module['name'].'</b><br />';
	// }
// }
?>
</pre>