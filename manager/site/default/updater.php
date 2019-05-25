<h1>Aktualizator</h1>
<?php
$api = $core->library->network->getJSONData($core->APIUpdater.'?date='.$core->releaseDate);
if($api === false)
	echo 'Nie udało się połączyć z API ('.$core->APIUpdater.')';
else{
	if($api['status'] === false)
		echo $api['description'];
	else{
		if($api['search']['count'] == 0){
			echo 'Nie znaleziono żadnych aktualizacji';
		}else{
			echo 'Znaleziono <b>'.$api['search']['count'].'</b> aktualizacji.<br />Lista znalezionych aktualizacji:
			<table class="title">
				<tr>
					<td>Wersja</td><td>Data</td><td>Typ</td>
				</tr>';
			foreach($api['list'] as $item){
				echo '<tr>
					<td>'.@$item['version'].'</td><td>'.@$item['date'].'</td><td>'.@$item['type'].'</td>
				</tr>';
			}
			echo '</table>
			<a href="index.php?type=default&page=updater&update=install" class="button">Zainstaluj aktualizacje</a><br />';
			if(isset($_GET['update'])){
				switch($_GET['update']){
					case 'install':
						echo '<br /><hr /><h1>Logi z instalacji</h1><pre>';
						foreach($api['list'] as $item){
							$temp = $core->path['dir_temp'].'update.zip';
							echo '> <b>Instalacja aktualizacji '.$item['version'].' ('.$item['date'].')</b><br />';
							echo '> Pobieranie pliku `'.$item['file'].'` do '.$temp.'<br />';
							$core->library->network->downloadFile($item['file'], $temp);
							echo '> Wypakowywanie aktualizacji<br />';
							$core->library->exZip->unzip($temp, $core->reversion);
							echo '> Usuwanie pliku tymczasowego<br />';
							unlink($temp);
						}
						echo '> <b>Poprawnie zainstalowano aktualizacje</b>';
						echo '</pre>';
						break;
				}
			}
		}
	}
}
?>