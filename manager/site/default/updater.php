<h1><?php echo $lang->get('updater') ?></h1>
<?php
$api = $core->library->network->getJSONData($core->APIUpdater.'?date='.$core->releaseDate);
if($api === false)
	echo $lang->get('errorconnecttoapi').' ('.$core->APIUpdater.')';
else{
	if($api['status'] === false)
		echo $api['description'];
	else{
		if($api['search']['count'] == 0){
			echo $lang->get('nofindupdate');
		}else{
			echo $lang->get('find').' <b>'.$api['search']['count'].'</b> '.$lang->get('update').'.<br />'.$lang->get('updatelist').':
			<table class="title">
				<tr>
					<td>'.$lang->get('version').'</td><td>'.$lang->get('date').'</td><td>'.$lang->get('type').'</td>
				</tr>';
			foreach($api['list'] as $item){
				echo '<tr>
					<td>'.@$item['version'].'</td><td>'.@$item['date'].'</td><td>'.@$item['type'].'</td>
				</tr>';
			}
			echo '</table>
			<a href="index.php?type=default&page=updater&update=install" class="button">'.$lang->get('installupdate').'</a><br />';
			if(isset($_GET['update'])){
				switch($_GET['update']){
					case 'install':
						echo '<br /><hr /><h1>'.$lang->get('installlog').'</h1><pre>';
						foreach($api['list'] as $item){
							$temp = $core->path['dir_temp'].'update.zip';
							echo '> <b>'.$lang->get('installingupdate').' '.$item['version'].' ('.$item['date'].')</b><br />';
							echo '> '.$lang->get('downloadfile').' `'.$item['file'].'` '.$lang->get('do').' '.$temp.'<br />';
							$core->library->network->downloadFile($item['file'], $temp);
							echo '> '.$lang->get('unzipupdate').'<br />';
							$core->library->exZip->unzip($temp, $core->reversion);
							echo '> '.$lang->get('deletetempfile').'<br />';
							unlink($temp);
						}
						echo '> <b>'.$lang->get('successupdateinstall').'</b>';
						echo '</pre>';
						break;
				}
			}
		}
	}
}
?>